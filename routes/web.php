<?php

use App\Http\Controllers\Bot\UpdateController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/<token>/webhook', function () {
    $update = Telegram::commandsHandler(true);

    // Commands handler method returns the Update object.
    // So you can further process $update object
    // to however you want.

    return 'ok';
});

Route::post('/platforms/{token}/webhook/task-completed', [UpdateController::class, 'taskCompleted'])->name('task-completed.webhook')
    ->whereIn('token', [env('APP_TOKEN')]);
Route::post('/platforms/{token}/webhook/new-task', [UpdateController::class, 'newTask'])->name('new-task.webhook')->whereIn('token', [env('APP_TOKEN')]);
Route::post('/platforms/{token}/register', [UpdateController::class, 'register'])->name('register.webhook')->whereIn('token', [env('APP_TOKEN')]);

//     $user = User::firstWhere([
//         'username' => $request->twitterScreenName,
//     ]);

//     if ($action === 'task-completed') {
//         if ($user) {
//             $telegram->sendMessage([
//                 'chat_id' => $user->chat_id,
//                 'text' => "🌈 Task Completed: $request->questDetails!
//             🏆 You've just gained $request->rewardPoints points, increasing your tally to $request->totalPoints points. Continue your engagement for even more exciting rewards! 🌟

//             Spread the word by sharing your referral link. Earn points for every friend who joins the SUPIDO journey! 🌍
//             [Your SUPIDO Referral Link]($user->referral_link)
//             ",
//                 'parse_mode' => 'MarkdownV2',
//             ]);
//             return response(['error' => false, 'message' => 'Message sent!']);
//         }

//         return response(['error' => true, 'message' => 'User hasn\'t starred a conversation with the SUPIDO Bot'], 404);
//     } else {
//     }
// });
