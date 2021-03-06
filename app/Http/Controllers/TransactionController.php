<?php

namespace Bdgt\Http\Controllers;

use Bdgt\Http\Requests\StoreTransactionRequest;
use Bdgt\Http\Requests\UpdateTransactionRequest;
use Bdgt\Repositories\Contracts\TransactionRepositoryInterface;
use Bdgt\Resources\Account;
use Bdgt\Resources\Category;
use Bdgt\Resources\Ledger;
use Illuminate\Support\Facades\Input;

class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(TransactionRepositoryInterface $transactionRepository)
    {
        $this->repository = $transactionRepository;
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index()
    {
        $ledger = new Ledger($this->repository);

        $c['ledger'] = $ledger;

        $c['accounts'] = Account::all();

        $c['categories'] = Category::all();

        return view('transaction.index', $c)->nest('transactions', 'transaction._list', [ 'transactions' => $ledger->transactions(), 'actionable' => true ]);
    }

    /**
     * Show an individual transaction to the user.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id)
    {
        try {
            $transaction = $this->repository->find($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect('/transactions');
        }

        return response()->json($transaction->toArray());
    }

    /**
     * Create and store a new transaction.
     *
     * @return Redirect
     */
    public function store(StoreTransactionRequest $request)
    {
        if ($this->repository->create(Input::except(['_token', '_method']))) {
            return redirect()->back()->with('alerts.success', trans('crud.transactions.created'));
        }
        return redirect()->back()->with('alerts.danger', trans('crud.transactions.error'));
    }

    /**
     * Update an existing transaction with new data.
     *
     * @param  int $id
     *
     * @return Redirect
     */
    public function update(UpdateTransactionRequest $request, $id)
    {
        if ($this->repository->update(Input::except(['_token', '_method']), $id)) {
            return redirect()->back()->with('alerts.success', trans('crud.transactions.updated'));
        }
        return redirect()->back()->with('alerts.danger', trans('crud.transactions.error'));
    }

    /**
     * Delete a transaction by ID.
     *
     * @param  int $id
     *
     * @return Redirect
     */
    public function destroy($id)
    {
        if ($this->repository->delete($id)) {
            return redirect('/transactions')->with('alerts.success', trans('crud.transactions.deleted'));
        } else {
            return redirect()->back()->with('alerts.danger', trans('crud.transactions.error'));
        }
    }
}
