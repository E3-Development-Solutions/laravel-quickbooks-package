<?php

namespace E3DevelopmentSolutions\QuickBooks\Http\Controllers;

use Illuminate\Http\Request;
use E3DevelopmentSolutions\QuickBooks\Services\QuickBooksBaseService;
use E3DevelopmentSolutions\QuickBooks\Exceptions\QuickBooksAuthException;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;

class QuickBooksAuthController extends Controller
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
            $state = uniqid('', true);
            
            // Store the state in the session with the authenticated user's ID
            session([
                'quickbooks_oauth_state' => $state,
                'quickbooks_user_id' => auth()->id()
            ]);
            
            $authUrl = $this->quickBooksService->getAuthorizationUrl($state);
            return redirect()->away($authUrl);
        } catch (QuickBooksAuthException $e) {
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
                'all_params' => $request->all(),
                'headers' => $request->headers->all(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $code = $request->input('code');
            $realmId = $request->input('realmId');
            $state = $request->input('state');
            
            Log::debug('QuickBooks OAuth Callback Parameters:', [
                'code' => $code ? '***REDACTED***' : null,
                'realmId' => $realmId,
                'state' => $state,
            ]);
            
            if (! $code || ! $realmId || ! $state) {
                $error = $request->input('error');
                $errorDescription = $request->input('error_description');
                
                Log::error('QuickBooks OAuth Callback Error:', [
                    'error' => $error,
                    'error_description' => $errorDescription,
                ]);
                
                throw new QuickBooksAuthException('Invalid callback parameters. ' . ($error ? "Error: {$error} - {$errorDescription}" : ''));
            }
            
            // Verify the state matches what we stored
            if (!session()->has('quickbooks_oauth_state') || $state !== session('quickbooks_oauth_state')) {
                throw new QuickBooksAuthException('Invalid state parameter.');
            }
            
            // Get the user ID from the session
            $userId = session('quickbooks_user_id');
            
            if (!$userId) {
                throw new QuickBooksAuthException('Unable to determine authenticated user.');
            }
            
            Log::info('Processing QuickBooks OAuth callback', [
                'user_id' => $userId,
                'realm_id' => $realmId,
            ]);
            
            // Process the callback with the user ID
            $result = $this->quickBooksService->processCallback($code, $realmId, $userId);
            
            // Clear the session state
            session()->forget(['quickbooks_oauth_state', 'quickbooks_user_id']);
            
            Log::info('Successfully processed QuickBooks OAuth callback', [
                'user_id' => $userId,
                'realm_id' => $realmId,
            ]);
            
            return redirect()->route('dashboard')
                ->with('success', 'Successfully connected to QuickBooks!');
                
        } catch (QuickBooksAuthException $e) {
            Log::error('QuickBooks OAuth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Failed to connect to QuickBooks: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::critical('Unexpected QuickBooks OAuth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->except(['code', 'state']), // Don't log sensitive data
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'An unexpected error occurred while connecting to QuickBooks.');
        }
    }

    /**
     * Disconnect the user from QuickBooks.
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