<?php

namespace E3DevelopmentSolutions\QuickBooks\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use E3DevelopmentSolutions\QuickBooks\Services\QuickBooksBaseService;
use E3DevelopmentSolutions\QuickBooks\Exceptions\QuickBooksAuthException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QuickBooksAuthController extends BaseController
{
    /**
     * The QuickBooks base service instance.
     *
     * @var \E3DevelopmentSolutions\QuickBooks\Services\QuickBooksBaseService
     */
    protected $quickBooksService;

    /**
     * Create a new controller instance.
     *
     * @param  \E3DevelopmentSolutions\QuickBooks\Services\QuickBooksBaseService  $quickBooksService
     * @return void
     */
    public function __construct(QuickBooksBaseService $quickBooksService)
    {
        $this->quickBooksService = $quickBooksService;
    }

    /**
     * Redirect to QuickBooks for authorization.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connect()
    {
        try {
            $authUrl = $this->quickBooksService->getAuthorizationUrl();
            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            Log::error('QuickBooks Connect Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('dashboard')
                ->with('error', 'Failed to connect to QuickBooks: ' . $e->getMessage());
        }
    }

    /**
     * Handle the callback from QuickBooks.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        try {
            Log::info('QuickBooks OAuth Callback Received:', [
                'all_params' => $request->except(['code']), // Don't log the full code
                'headers' => array_map(function($header) { 
                    return is_array($header) ? implode(', ', $header) : $header; 
                }, $request->headers->all()),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $code = $request->input('code');
            $realmId = $request->input('realmId');
            
            Log::debug('QuickBooks OAuth Callback Parameters:', [
                'code' => $code ? '***REDACTED***' : null,
                'realmId' => $realmId,
            ]);
            
            if (! $code || ! $realmId) {
                $error = $request->input('error');
                $errorDescription = $request->input('error_description');
                
                Log::error('QuickBooks OAuth Callback Error - Missing Parameters:', [
                    'error' => $error,
                    'error_description' => $errorDescription,
                    'request_params' => $request->all(),
                ]);
                
                throw new QuickBooksAuthException('Invalid callback parameters. ' . ($error ? "Error: {$error} - {$errorDescription}" : ''));
            }

            $result = $this->quickBooksService->processCallback($code, $realmId, auth()->id());
            
            Log::info('QuickBooks OAuth Callback Processed Successfully', [
                'user_id' => auth()->id(),
                'realm_id' => $realmId,
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Successfully connected to QuickBooks!');
                
        } catch (QuickBooksAuthException $e) {
            Log::error('QuickBooks Auth Exception: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_params' => $request->all(),
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'QuickBooks Connection Error: ' . $e->getMessage());
                
        } catch (\Exception $e) {
            Log::error('QuickBooks Callback Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_params' => $request->all(),
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'An unexpected error occurred while connecting to QuickBooks. Please try again.');
        }
    }

    /**
     * Disconnect from QuickBooks.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect()
    {
        $userId = auth()->id();
        
        try {
            Log::info('Disconnecting user from QuickBooks', ['user_id' => $userId]);
            
            auth()->user()->disconnectFromQuickBooks();
            
            Log::info('Successfully disconnected user from QuickBooks', ['user_id' => $userId]);
            
            return redirect()->route('dashboard')
                ->with('success', 'Successfully disconnected from QuickBooks!');
                
        } catch (\Exception $e) {
            Log::error('Error disconnecting from QuickBooks: ' . $e->getMessage(), [
                'user_id' => $userId,
                'exception' => $e,
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Failed to disconnect from QuickBooks.');
        }
    }
}