<?php

namespace E3DevelopmentSolutions\QuickBooks\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use E3DevelopmentSolutions\QuickBooks\Services\QuickBooksBaseService;
use E3DevelopmentSolutions\QuickBooks\Exceptions\QuickBooksAuthException;

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
     * Redirect the user to the QuickBooks authorization page.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function connect()
    {
        $authUrl = $this->quickBooksService->getAuthorizationUrl();
        
        return redirect()->away($authUrl);
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
            $code = $request->input('code');
            $realmId = $request->input('realmId');
            
            if (! $code || ! $realmId) {
                throw new QuickBooksAuthException('Invalid callback parameters.');
            }
            
            $this->quickBooksService->processCallback($code, $realmId);
            
            return redirect()->route('filament.pages.dashboard')
                ->with('success', 'Successfully connected to QuickBooks!');
        } catch (QuickBooksAuthException $e) {
            return redirect()->route('filament.pages.dashboard')
                ->with('error', 'Failed to connect to QuickBooks: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect the user from QuickBooks.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disconnect()
    {
        auth()->user()->disconnectFromQuickBooks();
        
        return redirect()->route('filament.pages.dashboard')
            ->with('success', 'Successfully disconnected from QuickBooks!');
    }
}
