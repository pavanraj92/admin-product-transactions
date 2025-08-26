<?php

namespace admin\product_transactions\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use admin\product_transactions\Models\Transaction;

class TransactionManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admincan_permission:transactions_manager_list')->only(['index', 'list']);
        $this->middleware('admincan_permission:transactions_manager_view')->only(['show']);
    }

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        try {
            $transactions = Transaction::with('user')
                ->filter($request->only(['status', 'keyword']))
                ->filterByStatus($request->query('status'))
                ->orderBy('created_at', 'desc')
                ->sortable()
                ->paginate(Transaction::getPerPageLimit())
                ->withQueryString();

            return view('transaction::admin.index', compact('transactions'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load transactions: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new transaction.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        try {
            $transaction->load('user');
            return view('transaction::admin.show', compact('transaction'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load transaction: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified transaction from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
