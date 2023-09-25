<?php

use App\Helper\ResponseUtils;
use App\Http\Controllers\Api\Admin\AdminReportStatisticController;
use App\Http\Controllers\Api\Admin\CategoryServiceSellController;
use App\Http\Controllers\Api\User\Community\MoPostFindMotelController;
use App\Http\Controllers\Api\User\Community\MoPostRoommateController;
use App\Http\Controllers\Api\Admin\MoPostFindMotelController as AdminMoPostFindMotelController;
use App\Http\Controllers\Api\Admin\MoPostRoommateController as AdminMoPostRoommateController;
use App\Http\Controllers\Api\TingTingSmsController;
use App\Http\Controllers\Api\User\Community\CategoryServiceSellController as CommunityCategoryServiceSellController;
use App\Http\Controllers\Api\User\Manage\SupporterTowerController;
// use App\Http\Controllers\Api\PaymentMethod\NinePayController;
use App\Http\Controllers\PaymentMethod\NinePayController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::fallback(function () {
    return ResponseUtils::json([
        'code' => 404,
        'success' => false,
        'msg_code' => "NOT_FOUND",
        'msg' => "Trang không tồn tại",
    ]);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(["mid_res"])
    ->prefix('/')->group(function () {
        Route::get('/test_get', 'App\Http\Controllers\Api\TTestController@testGet');
        Route::get('/test_post', 'App\Http\Controllers\Api\TTestController@testPot');

        //Place
        Route::prefix('place')->group(function () {
            //App-Theme
            Route::get('/vn/{type}/{parent_id}', 'App\Http\Controllers\Api\User\PlaceController@getWithType');
            Route::get('/vn/{type}', 'App\Http\Controllers\Api\User\PlaceController@getWithType');
        });

        //device_token_user
        Route::prefix('device_token_user')->group(function () {
            Route::post('/', 'App\Http\Controllers\Api\User\UserDeviceTokenController@updateDeviceTokenUser')->middleware('user_auth');
            Route::delete('/', 'App\Http\Controllers\Api\User\UserDeviceTokenController@removeDeviceTokenUser');
        });

        Route::get('test_box_chats', 'App\Http\Controllers\Api\User\Community\UserMessageController@testBoxMess')->middleware('user_auth', 'check_phone_number');
        //Store user
        Route::prefix('user')->group(function () {

            ///////////////////// API OTHER /////////////////////

            //Đăng ký
            Route::post('register', 'App\Http\Controllers\Api\User\RegisterController@register');
            //Kiểm tra tài khoản
            Route::post('check_account', 'App\Http\Controllers\Api\User\RegisterController@checkAccount');
            //Kiểm tra tài khoản
            Route::post('check_username', 'App\Http\Controllers\Api\User\RegisterController@checkUsername');
            //Đăng nhập
            Route::post('login', 'App\Http\Controllers\Api\User\LoginController@login');

            Route::post('send_otp', [TingTingSmsController::class, 'send']);

            Route::get('login_apple', 'App\Http\Controllers\Api\User\LoginController@loginApple');

            Route::get('config_admins', 'App\Http\Controllers\Api\Admin\ConfigAdminController@getAdminConfig');

            //Đăng nhập social  
            Route::group(['middleware' => ['web']], function () {
                Route::get('redirect/{provider}', 'App\Http\Controllers\Api\User\LoginController@redirect');
                Route::get('callback/{provider}', 'App\Http\Controllers\Api\User\LoginController@callback');
            });

            //Cập nhật user
            Route::get('profile', 'App\Http\Controllers\Api\User\UserController@getProfile')->middleware('user_auth');
            Route::put('profile', 'App\Http\Controllers\Api\User\UserController@update')->middleware('user_auth');
            Route::put('profile/phone_number', 'App\Http\Controllers\Api\User\UserController@updatePhoneNumber')->middleware('user_auth');
            Route::put('profile/referral_code', 'App\Http\Controllers\Api\User\UserController@updateReferralCode')->middleware('user_auth');
            Route::put('host', 'App\Http\Controllers\Api\User\UserController@updateHost')->middleware('user_auth');

            //Danh sách Badges chi so user
            Route::get('badges', 'App\Http\Controllers\Api\BadgesController@getBadges')->middleware('able_null_user');

            //Danh sách Badges chi so motel
            Route::get('summary_motel', 'App\Http\Controllers\Api\User\BadgesController@getBadgesByMotel')->middleware('user_auth', 'check_phone_number');

            //Lịch sử thông báo
            Route::get('notifications_history', 'App\Http\Controllers\Api\NotificationUserController@getAll')->middleware('user_auth', 'check_phone_number');
            Route::post('notifications_history', 'App\Http\Controllers\Api\NotificationUserController@readAll')->middleware('user_auth', 'check_phone_number');
            Route::post('notifications_history/{notification_id}', 'App\Http\Controllers\Api\NotificationUserController@readANoti')->middleware('user_auth', 'check_phone_number');
            //Lấy thông báo theo userIds 
            Route::get('noti_unread', 'App\Http\Controllers\Api\NotificationUserController@getNotiUnread');

            //Handle Receiver Sms 
            Route::get('handle_receiver_sms', 'App\Http\Controllers\Api\HandleReceiverSmsController@handle');
            //send email otp
            Route::post('send_email_otp', 'App\Http\Controllers\Api\SendMailController@send_email_otp');

            //Lấy lại mật khẩu
            Route::post('reset_password', 'App\Http\Controllers\Api\User\LoginController@reset_password');
            //Thay đổi mật khẩu
            Route::post('change_password', 'App\Http\Controllers\Api\User\LoginController@change_password')->middleware('user_auth', 'check_phone_number');
            //Kiểm tra tồn tại
            Route::post('login/check_exists', 'App\Http\Controllers\Api\User\LoginController@check_exists');

            //Up 1 ảnh
            Route::post('images', 'App\Http\Controllers\Api\User\UploadImageController@upload')->middleware('user_auth', 'check_phone_number');
            //Up 1 video
            Route::post('videos', 'App\Http\Controllers\Api\User\UploadVideoController@upload')->middleware('user_auth', 'check_phone_number');

            //Cộng đồng
            Route::prefix('community')->group(function () {

                //Theme
                //Lấy contact admin
                Route::get('/admin_contact', 'App\Http\Controllers\Api\User\ThemeController@getContact');

                //Intro app
                Route::get('/intro_app', 'App\Http\Controllers\Api\User\Community\HomeController@introApp')->middleware('able_null_user');
                //Danh sách bài đăng home
                Route::get('/home_app', 'App\Http\Controllers\Api\User\Community\HomeController@homeApp')->middleware('able_null_user');
                //Tìm kiếm
                Route::get('/home_app/search', 'App\Http\Controllers\Api\User\Community\HomeController@search');
                //Lấy chi tiết khám phá
                Route::get('/home_app/discover_item/{discover_id}', 'App\Http\Controllers\Api\User\Community\HomeController@getDiscover');
                //Lấy bài đăng ở gần vị trí nhất
                Route::post('/post_loca_nearest', 'App\Http\Controllers\Api\User\Community\HomeController@getPostLocationNearest');

                //Statistic post call 
                Route::post('statistic_call_post/{post_id}', 'App\Http\Controllers\Api\User\Community\StatisticAdminController@addCallMoPost');
                Route::post('call_post_find_motel/{post_find_motel_id}', 'App\Http\Controllers\Api\User\Community\StatisticAdminController@addCallMoPostFindMotel');
                Route::post('call_post_roommate/{post_roommate_id}', 'App\Http\Controllers\Api\User\Community\StatisticAdminController@addCallMoPostFindRoommate');

                //Danh sách bài đăng
                Route::get('/mo_posts', 'App\Http\Controllers\Api\User\Community\MoPostController@getAll')->middleware('able_null_user');
                Route::post('/mo_posts', 'App\Http\Controllers\Api\User\Community\MoPostController@create')->middleware('user_auth', 'check_phone_number');
                Route::put('/mo_posts', 'App\Http\Controllers\Api\User\Community\MoPostController@update')->middleware('user_auth', 'check_phone_number');
                //Thông tin 1 bài đăng
                Route::get('/mo_posts/{mo_post_id}', 'App\Http\Controllers\Api\User\Community\MoPostController@getOne')->middleware('able_null_user');
                //Bài đăng tương tự
                Route::get('/mo_posts/similar_posts/{mo_post_id}', 'App\Http\Controllers\Api\User\Community\MoPostController@getSimilarPost')->middleware('able_null_user');

                // Bài đăng tìm phòng
                Route::get('/mo_post_find_motels', [MoPostFindMotelController::class, 'getAll'])->middleware('user_auth', 'check_phone_number');
                Route::post('/mo_post_find_motels', [MoPostFindMotelController::class, 'create'])->middleware('user_auth', 'check_phone_number');
                Route::put('/mo_post_find_motels/{mo_post_find_motel_id}', [MoPostFindMotelController::class, 'update'])->middleware('user_auth', 'check_phone_number');
                Route::get('/mo_post_find_motels/{mo_post_find_motel_id}', [MoPostFindMotelController::class, 'getOne'])->middleware('able_null_user');
                Route::delete('/mo_post_find_motels/{mo_post_find_motel_id}', [MoPostFindMotelController::class, 'delete'])->middleware('user_auth', 'check_phone_number');

                // Bài đăng tìm phòng ở ghép
                Route::get('/mo_post_roommates', [MoPostRoommateController::class, 'getAll'])->middleware('user_auth', 'check_phone_number');
                Route::post('/mo_post_roommates', [MoPostRoommateController::class, 'create'])->middleware('user_auth', 'check_phone_number');
                Route::put('/mo_post_roommates/{mo_post_roommate_id}', [MoPostRoommateController::class, 'update'])->middleware('user_auth', 'check_phone_number');
                Route::get('/mo_post_roommates/{mo_post_roommate_id}', [MoPostRoommateController::class, 'getOne'])->middleware('able_null_user');
                Route::delete('/mo_post_roommates/{mo_post_roommate_id}', [MoPostRoommateController::class, 'delete'])->middleware('user_auth', 'check_phone_number');

                //notification
                Route::get('/notifications_history', 'App\Http\Controllers\Api\User\Community\NotiController@getAll')->middleware('user_auth', 'check_phone_number');


                //update view post
                Route::put('/view_post', 'App\Http\Controllers\Api\User\ViewerPostController@update')->middleware('user_auth', 'check_phone_number');

                //category service sell
                Route::get('/category_service_sells', [CommunityCategoryServiceSellController::class, 'getAll'])->middleware('user_auth', 'check_phone_number');
                Route::get('/category_service_sells/{category_service_sell_id}', [CommunityCategoryServiceSellController::class, 'getOne'])->middleware('user_auth', 'check_phone_number');

                //service sell
                Route::get('/service_sells', 'App\Http\Controllers\Api\User\Community\ServiceSellController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::get('/service_sells/{service_sell_id}', 'App\Http\Controllers\Api\User\Community\ServiceSellController@getOne')->middleware('user_auth', 'check_phone_number');

                //Danh sách bài đăng yêu thích
                Route::get('/favorite_mo_posts', 'App\Http\Controllers\Api\User\Community\MoPostFavoriteController@getAll')->middleware('user_auth', 'check_phone_number');
                //Yêu thích bài đăng
                Route::post('/favorite_mo_posts/{mo_post_id}', 'App\Http\Controllers\Api\User\Community\MoPostFavoriteController@favorite')->middleware('user_auth', 'check_phone_number');

                //Danh sách bài đăng yêu thích
                Route::get('/favorite_motels', 'App\Http\Controllers\Api\User\Community\MotelFavoriteController@getAll')->middleware('user_auth', 'check_phone_number');
                //Yêu thích bài đăng
                Route::post('/favorite_motels/{mo_post_id}', 'App\Http\Controllers\Api\User\Community\MotelFavoriteController@favorite')->middleware('user_auth', 'check_phone_number');

                //Hợp đồng
                //Danh sách hợp đồng
                // Route::post('/contracts', 'App\Http\Controllers\Api\User\Community\ContractController@create')->middleware('user_auth', 'check_phone_number');
                Route::get('/contracts', 'App\Http\Controllers\Api\User\Community\ContractController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::get('/contracts/{contract_id}', 'App\Http\Controllers\Api\User\Community\ContractController@getOne')->middleware('user_auth', 'check_phone_number');
                // Route::delete('/contracts/{contract_id}', 'App\Http\Controllers\Api\User\Community\ContractController@delete')->middleware('user_auth', 'check_phone_number');
                Route::put('/contracts/{contract_id}', 'App\Http\Controllers\Api\User\Community\ContractController@update')->middleware('user_auth', 'check_phone_number');

                //Giỏ hàng
                //Danh sách Giỏ hàng
                Route::post('/cart_service_sells', 'App\Http\Controllers\Api\User\Community\CartServiceSellController@add')->middleware('user_auth', 'check_phone_number');
                Route::get('/cart_service_sells', 'App\Http\Controllers\Api\User\Community\CartServiceSellController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::delete('/cart_service_sells/{cart_id}', 'App\Http\Controllers\Api\User\Community\CartServiceSellController@delete')->middleware('user_auth', 'check_phone_number');
                Route::put('/cart_service_sells', 'App\Http\Controllers\Api\User\Community\CartServiceSellController@update')->middleware('user_auth', 'check_phone_number');

                //Báo cáo sự cố
                //Danh sách Sự cố
                Route::post('/report_problem', 'App\Http\Controllers\Api\User\Community\ReportProblemController@create')->middleware('user_auth', 'check_phone_number');
                Route::get('/report_problem', 'App\Http\Controllers\Api\User\Community\ReportProblemController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::get('/report_problem/{report_problem_id}', 'App\Http\Controllers\Api\User\Community\ReportProblemController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::delete('/report_problem/{report_problem_id}', 'App\Http\Controllers\Api\User\Community\ReportProblemController@delete')->middleware('user_auth', 'check_phone_number');
                Route::put('/report_problem/{report_problem_id}', 'App\Http\Controllers\Api\User\Community\ReportProblemController@update')->middleware('user_auth', 'check_phone_number');

                //Hóa đơn
                //Danh sách Hóa đơn
                Route::get('/bills', 'App\Http\Controllers\Api\User\Community\BillController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::get('/bills/{bill_id}', 'App\Http\Controllers\Api\User\Community\BillController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::put('/bills/{bill_id}', 'App\Http\Controllers\Api\User\Community\BillController@update')->middleware('user_auth', 'check_phone_number');

                //Đơn hàng
                Route::post('/order_service_sells/{order_code}/reorder', 'App\Http\Controllers\Api\User\Community\OrderServiceSellController@reorder')->middleware('user_auth', 'check_phone_number');
                Route::post('/order_service_sells/send_immediate', 'App\Http\Controllers\Api\User\Community\OrderServiceSellController@sendCartImmediate')->middleware('user_auth', 'check_phone_number');
                Route::post('/order_service_sells/send', 'App\Http\Controllers\Api\User\Community\OrderServiceSellController@sendCart')->middleware('user_auth', 'check_phone_number');
                Route::get('/order_service_sells', 'App\Http\Controllers\Api\User\Community\OrderServiceSellController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::get('/order_service_sells/{order_code}', 'App\Http\Controllers\Api\User\Community\OrderServiceSellController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::put('/order_service_sells/{order_code}', 'App\Http\Controllers\Api\User\Community\OrderServiceSellController@updateStatus')->middleware('user_auth', 'check_phone_number');

                //Địa chỉ bổ sung
                Route::post('/address_additions', 'App\Http\Controllers\Api\User\Community\AddressAdditionController@create')->middleware('user_auth', 'check_phone_number');
                Route::get('/address_additions', 'App\Http\Controllers\Api\User\Community\AddressAdditionController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::delete('/address_additions/{address_addition_id}', 'App\Http\Controllers\Api\User\Community\AddressAdditionController@delete')->middleware('user_auth', 'check_phone_number');
                Route::get('/address_additions/{address_addition_id}', 'App\Http\Controllers\Api\User\Community\AddressAdditionController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::put('/address_additions/{address_addition_id}', 'App\Http\Controllers\Api\User\Community\AddressAdditionController@update')->middleware('user_auth', 'check_phone_number');

                //Danh sách phòng
                Route::get('/motels', 'App\Http\Controllers\Api\User\Community\MotelController@getListRenterHasRenter')->middleware('user_auth', 'check_phone_number');
                Route::get('/motels/{motel_id}', 'App\Http\Controllers\Api\User\Community\MotelController@getOne')->middleware('user_auth', 'check_phone_number');

                //Danh sách admin helper và chủ nhà
                Route::get('/person_chat/admin_my_host', 'App\Http\Controllers\Api\User\Community\UserMessageController@getBoxAdminHost')->middleware('user_auth', 'check_phone_number');
                //Danh sách người chat với user
                Route::get('/person_chat', 'App\Http\Controllers\Api\User\Community\UserMessageController@getAllPerson')->middleware('user_auth', 'check_phone_number');

                Route::get('/person_chat/{person_chat_id}', 'App\Http\Controllers\Api\User\Community\UserMessageController@getOnePerson')->middleware('user_auth', 'check_phone_number');
                //Danh sách tin nhắn chat
                Route::get('/person_chat/{to_user_id}/messages', 'App\Http\Controllers\Api\User\Community\UserMessageController@getAllMessage')->middleware('user_auth', 'check_phone_number');
                //Chat cho user
                Route::post('/person_chat/{to_user_id}/messages', 'App\Http\Controllers\Api\User\Community\UserMessageController@sendMessage')->middleware('user_auth', 'check_phone_number');

                //Tìm phòng nhanh
                Route::get('/find_fast_motels', 'App\Http\Controllers\Api\User\FindFastMotelController@getAll')->middleware('able_null_user');
                Route::post('/find_fast_motels', 'App\Http\Controllers\Api\User\FindFastMotelController@create')->middleware('able_null_user');
                Route::put('/find_fast_motels/{find_fast_motel_id}', 'App\Http\Controllers\Api\User\FindFastMotelController@update')->middleware('able_null_user');
                Route::get('/find_fast_motels/{find_fast_motel_id}', 'App\Http\Controllers\Api\User\FindFastMotelController@getOne')->middleware('able_null_user');
                Route::delete('/find_fast_motels', 'App\Http\Controllers\Api\User\FindFastMotelController@delete')->middleware('able_null_user');

                //Giữ chỗ
                Route::get('/reservation_motel', 'App\Http\Controllers\Api\User\ReservationMotelController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::post('/reservation_motel', 'App\Http\Controllers\Api\User\ReservationMotelController@create')->middleware('able_null_user');
                Route::put('/reservation_motel/{reservation_motel_id}', 'App\Http\Controllers\Api\User\ReservationMotelController@update')->middleware('user_auth', 'check_phone_number');
                Route::get('/reservation_motel/{reservation_motel_id}', 'App\Http\Controllers\Api\User\ReservationMotelController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::delete('/reservation_motel', 'App\Http\Controllers\Api\User\ReservationMotelController@delete')->middleware('user_auth', 'check_phone_number');

                //Bài đăng trợ giúp
                Route::get('/category_help_post', 'App\Http\Controllers\Api\User\CategoryHelpPostController@getAll')->middleware('able_null_user');
                Route::get('/help_post', 'App\Http\Controllers\Api\User\HelpPostController@getAll')->middleware('able_null_user');
                Route::get('/help_post/{help_post_id}', 'App\Http\Controllers\Api\User\HelpPostController@getOne')->middleware('able_null_user');

                //Báo cáo vi phạm bài đăng
                Route::post('/report_post_violations', 'App\Http\Controllers\Api\User\ReportPostViolationController@create')->middleware('able_null_user');
                Route::post('/report_post_find_motel_violations', 'App\Http\Controllers\Api\User\ReportPostFindMotelViolationController@create')->middleware('able_null_user');
                Route::post('/report_post_roommate_violations', 'App\Http\Controllers\Api\User\ReportPostRoommateViolationController@create')->middleware('able_null_user');

                //Count statistic admin
                Route::post('/statistic_admin', 'App\Http\Controllers\Api\User\ReportPostViolationController@create')->middleware('able_null_user');

                // History e wallet collaborator
                Route::get('/e_wallet_histories', 'App\Http\Controllers\Api\User\EWalletCollaboratorController@getHistoryEWalletCollaborator')->middleware('user_auth', 'check_phone_number');

                // Yêu cầu rút tiền
                Route::post('request_withdrawals', 'App\Http\Controllers\Api\User\EWalletCollaboratorController@requestWithdrawal')->middleware('user_auth', 'check_phone_number');
                Route::get('/request_withdrawals', 'App\Http\Controllers\Api\User\EWalletCollaboratorController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::put('/request_withdrawals/{withdrawal_id}', 'App\Http\Controllers\Api\User\EWalletCollaboratorController@update')->middleware('user_auth', 'check_phone_number');
                Route::get('/request_withdrawals/{withdrawal_id}', 'App\Http\Controllers\Api\User\EWalletCollaboratorController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::delete('/request_withdrawals/{withdrawal_id}', 'App\Http\Controllers\Api\User\EWalletCollaboratorController@delete')->middleware('user_auth', 'check_phone_number');

                // Khách hàng tiềm năng
                Route::post('potential_user', 'App\Http\Controllers\Api\User\Community\PotentialController@create')->middleware('user_auth', 'check_phone_number');
            });

            //Quản lý
            Route::prefix('manage')->group(function () {
                //Phòng trọ
                //Danh sách phòng trọ
                Route::post('/motels', 'App\Http\Controllers\Api\User\Manage\MotelController@create')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/motels', 'App\Http\Controllers\Api\User\Manage\MotelController@getAll')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/motels/{motel_id}', 'App\Http\Controllers\Api\User\Manage\MotelController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::delete('/motels/{motel_id}', 'App\Http\Controllers\Api\User\Manage\MotelController@delete')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/motels/{motel_id}', 'App\Http\Controllers\Api\User\Manage\MotelController@update')->middleware('user_auth', 'check_phone_number', 'is_host');
                // DS phòng phân tòa quản lý
                Route::get('/supporter_manage_motels', 'App\Http\Controllers\Api\User\Manage\MotelController@getAllMotelManageTower')->middleware('user_auth', 'check_phone_number', 'is_host');

                //Dịch vụ cho tòa nhà
                //Danh sách dịch vụ
                Route::post('/services', 'App\Http\Controllers\Api\User\Manage\ServiceController@create')->middleware('user_auth', 'check_phone_number');
                Route::get('/services', 'App\Http\Controllers\Api\User\Manage\ServiceController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::get('/services/{service_id}', 'App\Http\Controllers\Api\User\Manage\ServiceController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::delete('/services/{service_id}', 'App\Http\Controllers\Api\User\Manage\ServiceController@delete')->middleware('user_auth', 'check_phone_number');
                Route::put('/services/{service_id}', 'App\Http\Controllers\Api\User\Manage\ServiceController@update')->middleware('user_auth', 'check_phone_number');

                //Dịch vụ cho phòng
                //Danh sách dịch vụ
                Route::post('/mo_services', 'App\Http\Controllers\Api\User\Manage\MoServiceController@create')->middleware('user_auth', 'check_phone_number');
                Route::get('/mo_services/{motel_id}', 'App\Http\Controllers\Api\User\Manage\MoServiceController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::get('/mo_services/motel_id/{mo_service_id}', 'App\Http\Controllers\Api\User\Manage\MoServiceController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::delete('/mo_services/{mo_service_id}', 'App\Http\Controllers\Api\User\Manage\MoServiceController@delete')->middleware('user_auth', 'check_phone_number');
                Route::put('/mo_services/{mo_service_id}', 'App\Http\Controllers\Api\User\Manage\MoServiceController@update')->middleware('user_auth', 'check_phone_number');

                //Người thuê
                //Danh sách Người thuê
                Route::post('/renters', 'App\Http\Controllers\Api\User\Manage\RenterController@create')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/renters', 'App\Http\Controllers\Api\User\Manage\RenterController@getAll')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/renters/{renter_id}', 'App\Http\Controllers\Api\User\Manage\RenterController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::delete('/renters/{renter_id}', 'App\Http\Controllers\Api\User\Manage\RenterController@delete')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/renters/{renter_id}', 'App\Http\Controllers\Api\User\Manage\RenterController@update')->middleware('user_auth', 'check_phone_number', 'is_host');

                //Bài đăng tìm người
                Route::post('/mo_posts', 'App\Http\Controllers\Api\User\Manage\MotelPostController@create')->middleware('user_auth', 'check_phone_number');
                Route::get('/mo_posts', 'App\Http\Controllers\Api\User\Manage\MotelPostController@getAll')->middleware('able_null_user');
                Route::get('/mo_posts/{mo_post_id}', 'App\Http\Controllers\Api\User\Manage\MotelPostController@getOne')->middleware('able_null_user');;
                Route::delete('/mo_posts/{mo_post_id}', 'App\Http\Controllers\Api\User\Manage\MotelPostController@delete')->middleware('user_auth', 'check_phone_number');
                Route::put('/mo_posts/update_status/{mo_post_id}', 'App\Http\Controllers\Api\User\Manage\MotelPostController@updateStatus')->middleware('user_auth', 'check_phone_number');
                Route::put('/mo_posts/{mo_post_id}', 'App\Http\Controllers\Api\User\Manage\MotelPostController@update')->middleware('user_auth', 'check_phone_number');

                //Hợp đồng
                //Danh sách hợp đồng
                Route::post('/contracts', 'App\Http\Controllers\Api\User\Manage\ContractController@create')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/contracts', 'App\Http\Controllers\Api\User\Manage\ContractController@getAll')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/contracts/{contract_id}', 'App\Http\Controllers\Api\User\Manage\ContractController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::delete('/contracts/{contract_id}', 'App\Http\Controllers\Api\User\Manage\ContractController@delete')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/contracts/{contract_id}', 'App\Http\Controllers\Api\User\Manage\ContractController@update')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/contracts/status_contracts/{contract_id}', 'App\Http\Controllers\Api\User\Manage\ContractController@updateStatusContract')->middleware('user_auth', 'check_phone_number');

                //Hóa đơn
                //Danh sách Hóa đơn
                Route::post('/bills', 'App\Http\Controllers\Api\User\Manage\BillController@create')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/bills', 'App\Http\Controllers\Api\User\Manage\BillController@getAll')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/bills/{bill_id}', 'App\Http\Controllers\Api\User\Manage\BillController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::delete('/bills/{bill_id}', 'App\Http\Controllers\Api\User\Manage\BillController@delete')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/bills/{bill_id}', 'App\Http\Controllers\Api\User\Manage\BillController@update')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/bill_status/{bill_id}', 'App\Http\Controllers\Api\User\Manage\BillController@updateStatus')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/bills_gen/{motel_id}', 'App\Http\Controllers\Api\User\Manage\BillController@getLatestBillByMotel')->middleware('user_auth', 'check_phone_number', 'is_host');

                //Giữ chỗ
                Route::get('/reservation_motel', 'App\Http\Controllers\Api\User\Manage\ReservationMotelController@getAll')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::post('/reservation_motel', 'App\Http\Controllers\Api\User\Manage\ReservationMotelController@create')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/reservation_motel/{reservation_motel_id}', 'App\Http\Controllers\Api\User\Manage\ReservationMotelController@update')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/reservation_motel/{reservation_motel_id}', 'App\Http\Controllers\Api\User\Manage\ReservationMotelController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::delete('/reservation_motel/{reservation_motel_id}', 'App\Http\Controllers\Api\User\Manage\ReservationMotelController@delete')->middleware('user_auth', 'check_phone_number', 'is_host');

                //Báo cáo
                Route::get('/overview', 'App\Http\Controllers\Api\User\Manage\ReportStatisticController@getRevenueByYears')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/statistic_revenue', 'App\Http\Controllers\Api\User\Manage\ReportStatisticController@getRevenueByYears')->middleware('user_auth', 'check_phone_number', 'is_host');
                // Route::get('/bills/{bill_id}', 'App\Http\Controllers\Api\User\Manage\BillController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');

                //Báo cáo sự cố
                Route::get('/report_problem/{report_problem_id}', 'App\Http\Controllers\Api\User\Manage\ReportProblemController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/report_problem', 'App\Http\Controllers\Api\User\Manage\ReportProblemController@getAll')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/report_problem/{report_problem_id}', 'App\Http\Controllers\Api\User\Manage\ReportProblemController@update')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::delete('/report_problem/{report_problem_id}', 'App\Http\Controllers\Api\User\Manage\ReportProblemController@delete')->middleware('user_auth', 'check_phone_number', 'is_host');

                //Quản lý hoa hồng
                Route::get('/commission_collaborator', 'App\Http\Controllers\Api\User\Manage\CommissionController@getAllCommission')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/commission_collaborator/{commission_collaborator_id}', 'App\Http\Controllers\Api\User\Manage\CommissionController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/commission_collaborator/{commission_collaborator_id}', 'App\Http\Controllers\Api\User\Manage\CommissionController@update')->middleware('user_auth', 'check_phone_number', 'is_host');
                // Route::delete('/report_problem/{report_problem_id}', 'App\Http\Controllers\Api\User\Manage\ReportProblemController@delete')->middleware('user_auth', 'check_phone_number', 'is_host');

                //notification
                Route::get('/notifications_history', 'App\Http\Controllers\Api\User\Manage\NotiController@getAll')->middleware('user_auth', 'check_phone_number', 'is_host');

                // Khách hàng tiềm năng
                Route::get('/history_potential_user', 'App\Http\Controllers\Api\User\Manage\PotentialController@getAllHistoryPotential')->middleware('user_auth', 'check_phone_number');
                Route::get('/potential_user', 'App\Http\Controllers\Api\User\Manage\PotentialController@getAll')->middleware('user_auth', 'check_phone_number');
                Route::get('/potential_user/{potential_user_id}', 'App\Http\Controllers\Api\User\Manage\PotentialController@getOne')->middleware('user_auth', 'check_phone_number');
                Route::put('/potential_user/{potential_user_id}', 'App\Http\Controllers\Api\User\Manage\PotentialController@update')->middleware('user_auth', 'check_phone_number');
                Route::delete('/potential_user/{potential_user_id}', 'App\Http\Controllers\Api\User\Manage\PotentialController@delete')->middleware('user_auth', 'check_phone_number');

                // Tòa nhà
                Route::post('/towers', 'App\Http\Controllers\Api\User\Manage\TowerController@create')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/towers', 'App\Http\Controllers\Api\User\Manage\TowerController@getAll')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::get('/towers/{tower_id}', 'App\Http\Controllers\Api\User\Manage\TowerController@getOne')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::delete('/towers/{tower_id}', 'App\Http\Controllers\Api\User\Manage\TowerController@delete')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::put('/towers/{tower_id}', 'App\Http\Controllers\Api\User\Manage\TowerController@update')->middleware('user_auth', 'check_phone_number', 'is_host');
                // thêm phòng vào tòa nhà
                Route::get('/tower_motels/{tower_motel_id}', 'App\Http\Controllers\Api\User\Manage\TowerMotelController@getAllMotelTower')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::post('/tower_motels', 'App\Http\Controllers\Api\User\Manage\TowerMotelController@addMotelTower')->middleware('user_auth', 'check_phone_number', 'is_host');
                Route::delete('/tower_motels/{tower_motel_id}', 'App\Http\Controllers\Api\User\Manage\TowerMotelController@deleteMotelTower')->middleware('user_auth', 'check_phone_number', 'is_host');

                // Quản lý tòa nhà
                Route::post('/supporter_manage_towers', [SupporterTowerController::class, 'create'])->middleware('user_auth', 'check_phone_number');
                Route::get('/supporter_manage_towers', [SupporterTowerController::class, 'getAll'])->middleware('user_auth', 'check_phone_number');
                Route::get('/supporter_manage_towers/{supporter_manage_tower_id}', [SupporterTowerController::class, 'getOne'])->middleware('user_auth', 'check_phone_number');
                Route::put('/supporter_manage_towers/{supporter_manage_tower_id}', [SupporterTowerController::class, 'update'])->middleware('user_auth', 'check_phone_number');
                Route::delete('/supporter_manage_towers', [SupporterTowerController::class, 'delete'])->middleware('user_auth', 'check_phone_number');
                // Thêm tòa quản lý
                Route::post('/supporter_manage_towers/add_towers', [SupporterTowerController::class, 'createTower'])->middleware('user_auth', 'check_phone_number');
                // Chỉnh sửa tòa quản lý
                Route::put('/supporter_manage_towers/update_towers', [SupporterTowerController::class, 'updateTower'])->middleware('user_auth', 'check_phone_number');
                // Xóa tòa quản lý
                Route::delete('/supporter_manage_towers/delete_towers', [SupporterTowerController::class, 'deleteTowerManageSupport'])->middleware('user_auth', 'check_phone_number');
                // Thêm phòng quản lý
                Route::post('/supporter_manage_towers/add_motels', [SupporterTowerController::class, 'createTower'])->middleware('user_auth', 'check_phone_number');
                // Chỉnh sửa phòng quản lý
                Route::put('/supporter_manage_towers/update_motels', [SupporterTowerController::class, 'updateTower'])->middleware('user_auth', 'check_phone_number');
            });
        });


        //admin
        Route::prefix('admin')->group(function () {
            // total_golden_coin,total_silver_coin,total_deposit,total_withdraw
            Route::get('/dashboard', 'App\Http\Controllers\Api\Admin\DashboardController@index')->middleware('admin_auth', 'permission_admin');

            //Phòng trọ
            //Danh sách phòng trọ is add
            Route::get('/motels', 'App\Http\Controllers\Api\Admin\MotelController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/motels/{motel_id}', 'App\Http\Controllers\Api\Admin\MotelController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::delete('/motels/{motel_id}', 'App\Http\Controllers\Api\Admin\MotelController@delete')->middleware('admin_auth', 'permission_admin');


            //Danh mục dịch vụ bán
            Route::get('/category_service_sells', [CategoryServiceSellController::class, 'getAll'])->middleware('admin_auth', 'permission_admin');
            Route::get('/category_service_sells/{category_service_sell_id}', [CategoryServiceSellController::class, 'getOne'])->middleware('admin_auth', 'permission_admin');
            Route::delete('/category_service_sells/{category_service_sell_id}', [CategoryServiceSellController::class, 'delete'])->middleware('admin_auth', 'permission_admin');
            Route::post('/category_service_sells', [CategoryServiceSellController::class, 'create'])->middleware('admin_auth', 'permission_admin');
            Route::put('/category_service_selltỉnhs/{category_service_sell_id}', [CategoryServiceSellController::class, 'update'])->middleware('admin_auth', 'permission_admin');

            //Dịch vụ bán
            Route::get('/service_sells', 'App\Http\Controllers\Api\Admin\ServiceSellController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/service_sells/{service_sell_id}', 'App\Http\Controllers\Api\Admin\ServiceSellController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::delete('/service_sells/{service_sell_id}', 'App\Http\Controllers\Api\Admin\ServiceSellController@delete')->middleware('admin_auth', 'permission_admin');
            Route::post('/service_sells', 'App\Http\Controllers\Api\Admin\ServiceSellController@create')->middleware('admin_auth', 'permission_admin');
            Route::put('/service_sells/{service_sell_id}', 'App\Http\Controllers\Api\Admin\ServiceSellController@update')->middleware('admin_auth', 'permission_admin');


            //Bài đăng tìm người         // User update
            Route::get('/mo_posts', 'App\Http\Controllers\Api\Admin\MotelPostController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/mo_posts/{post_id}', 'App\Http\Controllers\Api\Admin\MotelPostController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::put('/mo_posts/{post_id}', 'App\Http\Controllers\Api\Admin\MotelPostController@update')->middleware('admin_auth', 'permission_admin');
            Route::delete('/mo_posts/{post_id}', 'App\Http\Controllers\Api\Admin\MotelPostController@delete')->middleware('admin_auth', 'permission_admin');

            // Bài đăng tìm phòng
            Route::get('/mo_post_find_motels', [AdminMoPostFindMotelController::class, 'getAll'])->middleware('admin_auth', 'permission_admin');
            Route::post('/mo_post_find_motels', [AdminMoPostFindMotelController::class, 'create'])->middleware('admin_auth', 'permission_admin');
            Route::put('/mo_post_find_motels', [AdminMoPostFindMotelController::class, 'update'])->middleware('admin_auth', 'permission_admin');
            Route::put('/mo_post_find_motels/{mo_post_find_motel_id}/update_status', [AdminMoPostFindMotelController::class, 'updateStatus'])->middleware('admin_auth', 'permission_admin');
            Route::get('/mo_post_find_motels/{mo_post_find_motel_id}', [AdminMoPostFindMotelController::class, 'getOne'])->middleware('admin_auth', 'permission_admin');
            Route::delete('/mo_post_find_motels/{mo_post_find_motel_id}', [AdminMoPostFindMotelController::class, 'delete'])->middleware('admin_auth', 'permission_admin');

            // Bài đăng tìm phòng ở ghép
            Route::get('/mo_post_roommates', [AdminMoPostRoommateController::class, 'getAll'])->middleware('admin_auth', 'permission_admin');
            Route::post('/mo_post_roommates', [AdminMoPostRoommateController::class, 'create'])->middleware('admin_auth', 'permission_admin');
            Route::put('/mo_post_roommates/{mo_post_roommate_id}', [AdminMoPostRoommateController::class, 'update'])->middleware('admin_auth', 'permission_admin');
            Route::put('/mo_post_roommates/{mo_post_roommate_id}/update_status', [AdminMoPostRoommateController::class, 'updateStatus'])->middleware('admin_auth', 'permission_admin');
            Route::get('/mo_post_roommates/{mo_post_roommate_id}', [AdminMoPostRoommateController::class, 'getOne'])->middleware('admin_auth', 'permission_admin');
            Route::delete('/mo_post_roommates/{mo_post_roommate_id}', [AdminMoPostRoommateController::class, 'delete'])->middleware('admin_auth', 'permission_admin');

            //Users
            Route::get('/users', 'App\Http\Controllers\Api\Admin\UserController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/users/{user_id}', 'App\Http\Controllers\Api\Admin\UserController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::post('/users/{user_id}/set_host', 'App\Http\Controllers\Api\Admin\UserController@updateHost')->middleware('admin_auth', 'permission_admin');
            Route::put('/users/{user_id}', 'App\Http\Controllers\Api\Admin\UserController@updateUser')->middleware('admin_auth', 'permission_admin');
            Route::delete('/users/{user_id}', 'App\Http\Controllers\Api\Admin\UserController@delete')->middleware('admin_auth', 'permission_admin');

            //Renters
            Route::post('/masters', 'App\Http\Controllers\Api\Admin\RenterController@createMaster')->middleware('user_auth', 'permission_admin');
            Route::post('/renters', 'App\Http\Controllers\Api\Admin\RenterController@create')->middleware('user_auth', 'permission_admin');
            Route::get('/renters', 'App\Http\Controllers\Api\Admin\RenterController@getAll')->middleware('admin_auth', 'permission_admin');
            //GET RENTER BY USER ID
            Route::get('/renters/getRenterByUserid/{userId}', 'App\Http\Controllers\Api\Admin\RenterController@getRenterByUserid')->middleware('admin_auth', 'permission_admin');
            // GET MASTER BY USER ID
            Route::get('/masters/getMasterByUserid/{userId}', 'App\Http\Controllers\Api\Admin\RenterController@getMasterByUserid')->middleware('admin_auth', 'permission_admin');
            Route::get('/renters/{renter_id}', 'App\Http\Controllers\Api\Admin\RenterController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::put('/renters/{renter_id}', 'App\Http\Controllers\Api\Admin\RenterController@updateUser')->middleware('admin_auth', 'permission_admin');
            Route::delete('/renters/{renter_id}', 'App\Http\Controllers\Api\Admin\RenterController@delete')->middleware('admin_auth', 'permission_admin');

            //Banner
            Route::get('/banners', 'App\Http\Controllers\Api\Admin\AdminBannerController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/banners/{banner_id}', 'App\Http\Controllers\Api\Admin\AdminBannerController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::put('/banners/{banner_id}', 'App\Http\Controllers\Api\Admin\AdminBannerController@update')->middleware('admin_auth', 'permission_admin');
            Route::post('/banners', 'App\Http\Controllers\Api\Admin\AdminBannerController@create')->middleware('admin_auth', 'permission_admin');
            Route::delete('/banners', 'App\Http\Controllers\Api\Admin\AdminBannerController@delete')->middleware('admin_auth', 'permission_admin');

            //Loại bài đăng trợ giúp
            Route::get('/category_help_post', 'App\Http\Controllers\Api\Admin\AdminCategoryHelpPostController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/category_help_post/{category_help_post_id}', 'App\Http\Controllers\Api\Admin\AdminCategoryHelpPostController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::post('/category_help_post', 'App\Http\Controllers\Api\Admin\AdminCategoryHelpPostController@create')->middleware('admin_auth', 'permission_admin');
            Route::put('/category_help_post/{category_help_post_id}', 'App\Http\Controllers\Api\Admin\AdminCategoryHelpPostController@update')->middleware('admin_auth', 'permission_admin');
            Route::delete('/category_help_post/{category_help_post_id}', 'App\Http\Controllers\Api\Admin\AdminCategoryHelpPostController@delete')->middleware('admin_auth', 'permission_admin');

            //Bài đăng trợ giúp
            Route::get('/help_post', 'App\Http\Controllers\Api\Admin\AdminHelpPostController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/help_post/{help_post_id}', 'App\Http\Controllers\Api\Admin\AdminHelpPostController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::post('/help_post', 'App\Http\Controllers\Api\Admin\AdminHelpPostController@create')->middleware('admin_auth', 'permission_admin');
            Route::put('/help_post/{help_post_id}', 'App\Http\Controllers\Api\Admin\AdminHelpPostController@update')->middleware('admin_auth', 'permission_admin');
            Route::delete('/help_post/{help_post_id}', 'App\Http\Controllers\Api\Admin\AdminHelpPostController@delete')->middleware('admin_auth', 'permission_admin');

            //Theme
            //Khám phá
            Route::get('/admin_discovers', 'App\Http\Controllers\Api\Admin\AdminDiscoverController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::post('/admin_discovers', 'App\Http\Controllers\Api\Admin\AdminDiscoverController@create')->middleware('admin_auth', 'permission_admin');
            Route::put('/admin_discovers/{admin_discover_id}', 'App\Http\Controllers\Api\Admin\AdminDiscoverController@update')->middleware('admin_auth', 'permission_admin');
            Route::get('/admin_discovers/{admin_discover_id}', 'App\Http\Controllers\Api\Admin\AdminDiscoverController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::delete('/admin_discovers', 'App\Http\Controllers\Api\Admin\AdminDiscoverController@delete')->middleware('admin_auth', 'permission_admin');

            //Chi tiết khám phá
            Route::get('/admin_discover_items', 'App\Http\Controllers\Api\Admin\AdminDiscoverItemUiController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::post('/admin_discover_items', 'App\Http\Controllers\Api\Admin\AdminDiscoverItemUiController@create')->middleware('admin_auth', 'permission_admin');
            Route::put('/admin_discover_items/{admin_discover_item_id}', 'App\Http\Controllers\Api\Admin\AdminDiscoverItemUiController@update')->middleware('admin_auth', 'permission_admin');
            Route::get('/admin_discover_items/{admin_discover_item_id}', 'App\Http\Controllers\Api\Admin\AdminDiscoverItemUiController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::delete('/admin_discover_items', 'App\Http\Controllers\Api\Admin\AdminDiscoverItemUiController@delete')->middleware('admin_auth', 'permission_admin');

            //Hóa đơn dịch vụ bán
            Route::get('/order_service_sell', 'App\Http\Controllers\Api\Admin\AdminOrderServicesSellController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::post('/order_service_sell', 'App\Http\Controllers\Api\Admin\AdminOrderServicesSellController@create')->middleware('admin_auth', 'permission_admin');
            Route::put('/order_service_sell/{order_service_sell_id}', 'App\Http\Controllers\Api\Admin\AdminOrderServicesSellController@update')->middleware('admin_auth', 'permission_admin');
            Route::get('/order_service_sell/{order_service_sell_id}', 'App\Http\Controllers\Api\Admin\AdminOrderServicesSellController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::delete('/order_service_sell', 'App\Http\Controllers\Api\Admin\AdminOrderServicesSellController@delete')->middleware('admin_auth', 'permission_admin');

            //Tìm phòng nhanh
            Route::get('/find_fast_motels', 'App\Http\Controllers\Api\Admin\FindFastMotelController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::post('/find_fast_motels', 'App\Http\Controllers\Api\Admin\FindFastMotelController@create')->middleware('admin_auth', 'permission_admin');
            Route::put('/find_fast_motels/{find_fast_motel_id}', 'App\Http\Controllers\Api\Admin\FindFastMotelController@update')->middleware('admin_auth', 'permission_admin');
            Route::get('/find_fast_motels/{find_fast_motel_id}', 'App\Http\Controllers\Api\Admin\FindFastMotelController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::delete('/find_fast_motels/{find_fast_motel_id}', 'App\Http\Controllers\Api\Admin\FindFastMotelController@delete')->middleware('admin_auth', 'permission_admin');

            //Báo cáo bài đăng vi phạm
            Route::get('/report_post_violations', 'App\Http\Controllers\Api\Admin\ReportPostViolationController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_post_violations/{report_post_violation_id}', 'App\Http\Controllers\Api\Admin\ReportPostViolationController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::put('/report_post_violations/{report_post_violation_id}', 'App\Http\Controllers\Api\Admin\ReportPostViolationController@update')->middleware('admin_auth', 'permission_admin');
            Route::delete('/report_post_violations/{report_post_violation_id}', 'App\Http\Controllers\Api\Admin\ReportPostViolationController@delete')->middleware('admin_auth', 'permission_admin');

            //Giữ chỗ
            Route::get('/reservation_motel', 'App\Http\Controllers\Api\Admin\ReservationMotelController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/reservation_motel/{reservation_motel_id}', 'App\Http\Controllers\Api\Admin\ReservationMotelController@getOne')->middleware('admin_auth', 'permission_admin');

            //Users update needle
            Route::put('/users/{user_id}', 'App\Http\Controllers\Api\Admin\UserController@updateUser')->middleware('admin_auth', 'permission_admin');

            //Report statistic 
            Route::get('/report_statistic/badges', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@badges')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/statistic_resolved_problem', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getMinutesResolvedProblemHost')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/orders', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getOrdersService')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/service_sell', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getOrdersServiceBadges')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/users', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getUsers')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/renters', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getRenters')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/host_badges', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getHostBadges')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/motels', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getMotels')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/motel_badges', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getMotelBadges')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/mo_posts', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getMoPosts')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/mo_post_badges', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getMoPostBadges')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/find_fast_motels', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getFindFastMotels')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/find_fast_motel_badges', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getFindFastMotelBadges')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/reservation_motels', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getReservationMotels')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/reservation_motel_badges', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getReservationMotelBadges')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/commission_admin', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getCommissionAdmin')->middleware('admin_auth', 'permission_admin');
            // Route::get('/report_statistic/services', 'App\Http\Controllers\Api\Admin\AdminReportStatisticController@getServices')->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/mo_post_find_motels',  [AdminReportStatisticController::class, 'getMoPostFindMotels'])->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/mo_post_find_motel_badges', [AdminReportStatisticController::class, 'getMoPostFindMotelBadges'])->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/mo_post_roommates',  [AdminReportStatisticController::class, 'getMoPostFindRoommates'])->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/mo_post_roommate_badges', [AdminReportStatisticController::class, 'getMoPostFindRoommateBadges'])->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/potentials', [AdminReportStatisticController::class, 'getPotential'])->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/contracts', [AdminReportStatisticController::class, 'getContract'])->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/bills', [AdminReportStatisticController::class, 'getBills'])->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/potential_to_renters', [AdminReportStatisticController::class, 'getPotentialToRenters'])->middleware('admin_auth', 'permission_admin');
            Route::get('/report_statistic/potential_has_motels', [AdminReportStatisticController::class, 'getPotentialHasMotel'])->middleware('admin_auth', 'permission_admin');

            //Report problem 
            Route::get('/report_problem', 'App\Http\Controllers\Api\Admin\AdminReportProblemController@getAll')->middleware('admin_auth', 'permission_admin');

            //Danh sách tin nhắn chat
            Route::get('/person_chat/{user_id}/messages', 'App\Http\Controllers\Api\Admin\AdminMessageController@getAllMessage')->middleware('admin_auth', 'permission_admin');
            Route::get('/person_chat/{user_id}', 'App\Http\Controllers\Api\Admin\AdminMessageController@getAllPerson')->middleware('admin_auth', 'permission_admin');

            //Danh sách hợp đồng
            Route::get('/contracts', 'App\Http\Controllers\Api\Admin\AdminContractController@getAll')->middleware('admin_auth', 'permission_admin');

            //Danh sách hóa đơn
            Route::get('/bills', 'App\Http\Controllers\Api\Admin\AdminBillController@getAll')->middleware('admin_auth', 'permission_admin');

            //Admin contact
            Route::put('/admin_contact', 'App\Http\Controllers\Api\Admin\AdminContactController@update')->middleware('admin_auth', 'permission_admin');
            Route::get('/admin_contact', 'App\Http\Controllers\Api\Admin\AdminContactController@getContact')->middleware('admin_auth', 'permission_admin');

            //Admin notification
            Route::get('/notifications', 'App\Http\Controllers\Api\Admin\AdminNotiController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::post('/notifications', 'App\Http\Controllers\Api\Admin\AdminNotiController@create')->middleware('admin_auth', 'permission_admin');
            Route::post('/notifications_test', 'App\Http\Controllers\Api\Admin\AdminNotiController@CreateTest')->middleware('admin_auth', 'permission_admin');

            //Admin set role authorize
            Route::get('/system_permissions', 'App\Http\Controllers\Api\Admin\PermissionAdminController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::post('/system_permissions', 'App\Http\Controllers\Api\Admin\PermissionAdminController@create')->middleware('admin_auth', 'permission_admin');
            Route::put('/system_permissions/{system_permission_id}', 'App\Http\Controllers\Api\Admin\PermissionAdminController@update')->middleware('admin_auth', 'permission_admin');
            Route::get('/system_permissions/{system_permission_id}', 'App\Http\Controllers\Api\Admin\PermissionAdminController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::delete('/system_permissions/{system_permission_id}', 'App\Http\Controllers\Api\Admin\PermissionAdminController@delete')->middleware('admin_auth', 'permission_admin');

            // Danh sách duyệt rút tiền
            Route::get('/request_withdrawals', 'App\Http\Controllers\Api\Admin\EWalletCollaboratorController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::put('/request_withdrawals/{withdrawal_id}', 'App\Http\Controllers\Api\Admin\EWalletCollaboratorController@update')->middleware('admin_auth', 'permission_admin');
            Route::get('/request_withdrawals/{withdrawal_id}', 'App\Http\Controllers\Api\Admin\EWalletCollaboratorController@getOne')->middleware('admin_auth', 'permission_admin');
            Route::delete('/request_withdrawals/{withdrawal_id}', 'App\Http\Controllers\Api\Admin\EWalletCollaboratorController@delete')->middleware('admin_auth', 'permission_admin');

            // Biến động số dư cộng tác viên
            Route::get('/history_balance_change_collaborator/{user_id}', 'App\Http\Controllers\Api\Admin\EWalletCollaboratorController@getHistoryEWalletCollaborator')->middleware('admin_auth', 'permission_admin');

            // Danh sách cộng tác viên
            Route::get('/referrals', 'App\Http\Controllers\Api\Admin\ReferralController@getAll')->middleware('admin_auth', 'permission_admin');
            Route::get('/referrals/referred/{referral_code}', 'App\Http\Controllers\Api\Admin\ReferralController@getListUserUseReferralCode')->middleware('admin_auth', 'permission_admin');

            // Danh sách hoa hồng
            Route::get('/commission_collaborator', 'App\Http\Controllers\Api\Admin\CommissionController@listCommissionCollaborator')->middleware('admin_auth', 'permission_admin');
            Route::get('/commission_collaborator/{commission_collaborator_id}', 'App\Http\Controllers\Api\Admin\CommissionController@getOneCommissionCollaborator')->middleware('admin_auth', 'permission_admin');
            Route::put('/commission_collaborator/confirm_user/{commission_collaborator_id}', 'App\Http\Controllers\Api\Admin\CommissionController@updateCommissionCollaborator')->middleware('admin_auth', 'permission_admin');
            Route::put('/commission_collaborator/confirm_paid_admin/{commission_collaborator_id}', 'App\Http\Controllers\Api\Admin\CommissionController@updatePaidCommissionAdmin')->middleware('admin_auth', 'permission_admin');

            //Admin set user role authorize
            Route::put('/user_permissions', 'App\Http\Controllers\Api\Admin\UserPermissionController@update')->middleware('admin_auth', 'permission_admin');

            //Admin history received money host
            Route::get('/history_receive_commission', 'App\Http\Controllers\Api\Admin\EWalletCollaboratorController@getHistoryReceiveMoneyAdmin')->middleware('admin_auth', 'permission_admin');

            Route::post('/config_admins', 'App\Http\Controllers\Api\Admin\ConfigAdminController@setCurrentVersionAdmin');


            // Wallet withdrows  
            Route::post('/withdraws', 'App\Http\Controllers\Api\Admin\WalletTransactionController@createWalletWithdraws')->middleware('user_auth');
            Route::get('/deposits', 'App\Http\Controllers\Api\Admin\WalletTransactionController@getAllWalletDeposit')->middleware('user_auth', 'permission_admin');
            Route::get('/withdraws', 'App\Http\Controllers\Api\Admin\WalletTransactionController@getAllWalletWithdraws')->middleware('user_auth', 'permission_admin');
            Route::put('/withdraw/edit/{wallet_transaction_id}', 'App\Http\Controllers\Api\Admin\WalletTransactionController@editWalletWithdrow')->middleware('user_auth', 'permission_admin');

            Route::get('/getAllWalletDeposit/{user_id}', 'App\Http\Controllers\Api\Admin\WalletTransactionController@getAllWalletDepositbyUserId')->middleware('user_auth');
            Route::get('/getAllWalletWithdraw/{user_id}', 'App\Http\Controllers\Api\Admin\WalletTransactionController@getAllWalletWithdrawUserId')->middleware('user_auth');

            // admin review 
            Route::post('/confirm-payment-status', 'App\Http\Controllers\Api\Admin\WallentTransactionAdminReviewController@confirmPaymentStatusAdmin')->middleware('user_auth', 'permission_admin');
            // withdrwa request review by admin 
            Route::post('/withdraw_request_review_admin', 'App\Http\Controllers\Api\Admin\WallentTransactionAdminReviewController@withdraw_request')->middleware('user_auth', 'permission_admin');

            // Route::get('/wallet-data-for-graph', 'App\Http\Controllers\Api\Admin\WallentTransactionAdminReviewController@getWalletDataForGraph')->middleware('user_auth', 'permission_admin');
            Route::get('/wallet-data-for-graph', 'App\Http\Controllers\Api\Admin\WallentTransactionAdminReviewController@getWalletDataForGraph')->middleware('user_auth', 'permission_admin');

            // Route for get Tower by User id
            // Route::get('/getAllTower/{userId}', 'App\Http\Controllers\Api\User\Manage\TowerController@getUserWiseAllTower')->middleware('user_auth', 'permission_admin');

            Route::get('/get-all-tower/{userId}', 'App\Http\Controllers\Api\User\Manage\TowerController@getUserWiseAllTower')->middleware('user_auth', 'permission_admin');
            // Route for Tower room hidden or unhidden 
            // Route::put('/update-tower-motel/{motel_id}', 'App\Http\Controllers\Api\User\Manage\TowerMotelController@updateTowerByRoom')->middleware('user_auth', 'permission_admin');
            Route::post('/update-towetmotel-byroom', 'App\Http\Controllers\Api\User\Manage\TowerMotelController@updateTowerByRoom')->middleware('user_auth', 'permission_admin');

            // is my last message 
            Route::get('/user/community/person_chat/{user_id}', 'App\Http\Controllers\Api\Admin\AdminMessageController@getLatestMessage')->middleware('user_auth', 'permission_admin');

            //Set discount and update discount bill pay
            Route::resource('settings', App\Http\Controllers\Api\Admin\SettingsController::class)->middleware('user_auth', 'permission_admin');

            Route::post('contact_bill_by_wallet', 'App\Http\Controllers\Api\Admin\WalletTransactionController@contactBillByWallet')->middleware('user_auth', 'permission_admin');
            Route::post('montly_room_rent_bill_by_wallet', 'App\Http\Controllers\Api\Admin\WalletTransactionController@montlyRoomBillByWallet')->middleware('user_auth', 'permission_admin');
        });

        // Route for Wallet Transaction Bank List
        Route::get('/getUserBankList', 'App\Http\Controllers\Api\Admin\WalletTransactionBankListController@getUserBankList')->middleware('user_auth');


        /**
         * Wallet Transaction
         */
        Route::post('/deposits', 'App\Http\Controllers\Api\Admin\WalletTransactionController@createWalletDeposit')->middleware('user_auth');
        // Route::get('/deposits', 'App\Http\Controllers\Api\Admin\WalletTransactionController@getAllWalletDeposit')->middleware('user_auth');
        Route::put('/deposit/edit/{wallet_transaction_id}', 'App\Http\Controllers\Api\Admin\WalletTransactionController@editWalletDeposit')->middleware('user_auth');

        //Virtual Account
        Route::post('/virtual-account/create', 'App\Http\Controllers\PaymentMethod\NinePayController@createVirtualAccount')->middleware('user_auth');
        Route::post('/virtual-account/update', 'App\Http\Controllers\PaymentMethod\NinePayController@updateVirtualAccount')->middleware('user_auth');
        Route::post('/virtual-account/info', 'App\Http\Controllers\PaymentMethod\NinePayController@infoVirtualAccount')->middleware('user_auth');

        Route::post('/payments-create', 'App\Http\Controllers\PaymentMethod\NinePayController@paymentCreate')->middleware('user_auth');
        Route::get('/inquire', 'App\Http\Controllers\PaymentMethod\NinePayController@inquire')->middleware('user_auth');
        Route::post('/refunds-create', 'App\Http\Controllers\PaymentMethod\NinePayController@refundCreate')->middleware('user_auth');

        // Route::get('get/invoice/inquire','App\Http\Controllers\PaymentMethod\NinePayController@invoiceInquire')->middleware('user_auth', 'permission_admin');
        Route::get('/result', 'App\Http\Controllers\PaymentMethod\NinePayController@result');
        Route::post('/ipn-url ', 'App\Http\Controllers\PaymentMethod\NinePayController@ipnUrlWebhook');

        Route::post('/addBank', 'App\Http\Controllers\BankController@addBank')->middleware('user_auth');
        // Route::put('/edit_bank_info/{bankId}', 'App\Http\Controllers\BankController@update')->middleware('user_auth');
        Route::post('/edit_bank_info/{id}', 'App\Http\Controllers\BankController@update')->middleware('user_auth');
        Route::get('/getUserBankListbyUserId/{user_id}', 'App\Http\Controllers\BankController@getUserBankListbyUserId')->middleware('user_auth');
        // // Re post request to Admin 
        // Route::post('/user-send-request-admin', 'App\Http\Controllers\Api\NotificationUserController@sendRequest')->middleware('user_auth');
        // Route::post('/admin-approve-post-status', 'App\Http\Controllers\Api\User\Community\MoPostController@adminApprovedPost')->middleware('user_auth');
        Route::resource('settings', App\Http\Controllers\SettingsController::class);
    });
