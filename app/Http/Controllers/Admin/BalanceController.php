<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MoneyValidationFormRequest;
use Illuminate\Http\Request;
use App\Models\Balance;
use App\User;

class BalanceController extends Controller {

	public function index() {
		$balance = auth()->user()->balance;
		$amount  = $balance?$balance->amount:0;

		return view('admin.balance.index', compact('amount'));
	}

	public function deposit() {
		return view('admin.balance.deposit');
	}

	public function depositStore(MoneyValidationFormRequest $request) {
		$balance  = auth()->user()->balance()->firstOrCreate([]);
		$response = $balance->deposit($request->value);

		if ($response['success']) {
			return redirect()
				->route('admin.balance')
				->with('success', $response['message']);
		}

		return redirect()
			->back()
			->with('error', $response['message']);
	}

	public function withdraw() {
		return view('admin.balance.withdraw');
	}

	public function withdrawStore(MoneyValidationFormRequest $request) {
		$balance  = auth()->user()->balance()->firstOrCreate([]);
		$response = $balance->withdraw($request->value);

		if ($response['success']) {
			return redirect()
				->route('admin.balance')
				->with('success', $response['message']);
		}

		return redirect()
			->back()
			->with('error', $response['message']);
	}

	public function transfer()
	{
		return view('admin.balance.transfer');
	}

	public function confirmTransfer(Request $request, User $user)
	{
		if (!$sender = $user->getSender($request->sender)) {
				return redirect()
									->back()
									->with('error', 'Usuário Informado não foi Encontrado!');
		}

		if ($sender->id === auth()->user()->id) {
			return redirect()
								->back()
								->with('error', 'Não pode transferir para si mesmo!');
		}

		$balance = auth()->user()->balance;

		return view('admin.balance.transfer-confirm', compact('sender', 'balance'));
	}

	public function transferStore(MoneyValidationFormRequest $request, User $user)
	{
		if (!$sender = $user->find($request->sender_id)) {
			return redirect()
									->route('balance.transfer')
									->with('success', 'Recebedor não Encontrado!');
		}

		$balance  = auth()->user()->balance()->firstOrCreate([]);
		$response = $balance->transfer($request->value, $sender);

		if ($response['success']) {
			return redirect()
				->route('admin.balance')
				->with('success', $response['message']);
		}

		return redirect()
			->route('balance.transfer')
			->with('error', $response['message']);
		}

		public function historic()
		{
			$historics = auth()->user()->historics()->with(['userSender'])->get();

			return view('admin.balance.historics', compact('historics'));
		}

}
