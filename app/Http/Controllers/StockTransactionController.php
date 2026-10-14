<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuyTransactionRequest;
use App\Http\Requests\StoreSellTransactionRequest;
use App\Http\Requests\UpdateStockTransactionRequest;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use App\Services\Transactions\UpdateStockTransaction;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockTransactionController extends Controller
{
    /**
     * Show the form for recording a BUY transaction for a given company.
     */
    public function create(Request $request, Company $company): View
    {
        $user = $request->user();

        // Inactive company protection
        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot execute transactions for an inactive company.');
        }

        // Fetch user's current position for context in the BUY form
        $position = StockPosition::query()
            ->forUser($user)
            ->forCompany($company)
            ->first();

        return view('stocks.buy', [
            'company' => $company,
            'position' => $position,
        ]);
    }

    /**
     * Authoritative submission handler for executing a BUY order.
     */
    public function store(
        StoreBuyTransactionRequest $request,
        Company $company,
        BuyStock $buyStock
    ): RedirectResponse {
        $user = $request->user();

        // Inactive company protection
        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot execute transactions for an inactive company.');
        }

        // Hand off authoritative calculation and persistence to the domain service
        $buyStock->execute(
            user: $user,
            company: $company,
            quantity: $request->validated('quantity'),
            price: $request->validated('price'),
            transactionDate: $request->validated('transaction_date'),
            notes: $request->validated('notes')
        );

        return redirect()->route('stocks.show', $company)
            ->with('status', 'BUY transaction recorded successfully.');
    }

    /**
     * Show the form for recording a SELL transaction for a given company.
     */
    public function createSell(Request $request, Company $company): View
    {
        $user = $request->user();

        // Inactive company protection
        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot execute transactions for an inactive company.');
        }

        // Fetch user's current position for context in the SELL form
        $position = StockPosition::query()
            ->forUser($user)
            ->forCompany($company)
            ->first();

        return view('stocks.sell', [
            'company' => $company,
            'position' => $position,
        ]);
    }

    /**
     * Authoritative submission handler for executing a SELL order.
     */
    public function storeSell(
        StoreSellTransactionRequest $request,
        Company $company,
        SellStock $sellStock
    ): RedirectResponse {
        $user = $request->user();

        // Inactive company protection
        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot execute transactions for an inactive company.');
        }

        try {
            // Hand off authoritative calculation and persistence to the domain service
            $sellStock->execute(
                user: $user,
                company: $company,
                quantity: $request->validated('quantity'),
                price: $request->validated('price'),
                transactionDate: $request->validated('transaction_date'),
                notes: $request->validated('notes')
            );
        } catch (DomainException $e) {
            throw ValidationException::withMessages([
                'quantity' => $e->getMessage(),
            ]);
        }

        return redirect()->route('stocks.show', $company)
            ->with('status', 'SELL transaction recorded successfully.');
    }

    /**
     * Show the form for editing an existing transaction.
     */
    public function edit(Request $request, Company $company, StockTransaction $transaction): View
    {
        $user = $request->user();

        if ($transaction->user_id !== $user->id || $transaction->company_id !== $company->id) {
            abort(403, 'Unauthorized to access this transaction.');
        }

        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot edit transactions for an inactive company.');
        }

        $position = StockPosition::query()
            ->forUser($user)
            ->forCompany($company)
            ->first();

        return view('stocks.transactions.edit', [
            'company' => $company,
            'transaction' => $transaction,
            'position' => $position,
        ]);
    }

    /**
     * Authoritative submission handler for updating an existing transaction.
     */
    public function update(
        UpdateStockTransactionRequest $request,
        Company $company,
        StockTransaction $transaction,
        UpdateStockTransaction $updateStockTransaction
    ): RedirectResponse {
        $user = $request->user();

        if ($transaction->user_id !== $user->id || $transaction->company_id !== $company->id) {
            abort(403, 'Unauthorized to modify this transaction.');
        }

        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot edit transactions for an inactive company.');
        }

        try {
            $updateStockTransaction->execute(
                user: $user,
                company: $company,
                transaction: $transaction,
                quantity: $request->validated('quantity'),
                price: $request->validated('price'),
                transactionDate: $request->validated('transaction_date'),
                notes: $request->validated('notes')
            );
        } catch (DomainException $e) {
            throw ValidationException::withMessages([
                'quantity' => $e->getMessage(),
            ]);
        }

        return redirect()->route('stocks.show', $company)
            ->with('status', 'Transaction updated successfully and position recalculated.');
    }

    /**
     * Delete an existing transaction and recalculate position.
     */
    public function destroy(
        Request $request,
        Company $company,
        StockTransaction $transaction,
        UpdateStockTransaction $updateStockTransaction
    ): RedirectResponse {
        $user = $request->user();

        if ($transaction->user_id !== $user->id || $transaction->company_id !== $company->id) {
            abort(403, 'Unauthorized to delete this transaction.');
        }

        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot delete transactions for an inactive company.');
        }

        try {
            $updateStockTransaction->delete(
                user: $user,
                company: $company,
                transaction: $transaction
            );
        } catch (DomainException $e) {
            return redirect()->route('stocks.show', $company)
                ->withErrors(['quantity' => $e->getMessage()]);
        }

        return redirect()->route('stocks.show', $company)
            ->with('status', 'Transaction deleted successfully and position recalculated.');
    }
}
