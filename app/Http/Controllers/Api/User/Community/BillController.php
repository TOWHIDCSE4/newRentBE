<?php

namespace App\Http\Controllers\Api\User\Community;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MsgCode;
use App\Models\Bill;
use App\Helper\ResponseUtils;
use App\Helper\StatusBillDefineCode;
use App\Helper\ParamUtils;
use App\Helper\DatetimeUtils;
use App\Helper\Helper;
use App\Helper\NotiUserDefineCode;
use App\Helper\StatusMotelDefineCode;
use App\Helper\TypeFCM;
use App\Jobs\NotificationUserJob;
use App\Models\WalletTransaction;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Exception;

/**
 * @group Bill/Người dùng/quản lý hóa đơn
 */
class BillController extends Controller
{
    /**
     * 
     * Cập nhật 1 hóa đơn 
     * 
     * @bodyParam status
     * @bodyParam date_payment datetime ngày thanh toán
     * @bodyParam images array danh sách ảnh hóa đơn
     * @bodyParam motel_id int mã phòng
     * 
     */
    public function update(Request $request, $id)
    {
        $images = $request->images;
        $billExistedData = null;

        $motelExist = DB::table('motels', $request->motel_id)->first();
        if ($motelExist == null) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => false,
                'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                'msg' => MsgCode::NO_MOTEL_EXISTS[1]
            ]);
        }

        $billExisted = Bill::join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
            ->where([
                ['bills.id', $id],
                ['user_contracts.motel_id', $request->motel_id],
                ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                ['user_contracts.renter_phone_number', $request->user->phone_number],
            ])
            ->select('bills.*');

        if (!$billExisted->exists()) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::NO_BILL_EXISTS[0],
                'msg' => MsgCode::NO_BILL_EXISTS[1]
            ]);
        }

        if ($images != null || !empty($images)) {
            if (!is_array($images)) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'success' => false,
                    'msg_code' => MsgCode::INVALID_IMAGES[0],
                    'msg' => MsgCode::INVALID_IMAGES[1],
                ]);
            }

            if (count($images) > 0) {
                foreach ($images as $imageItem) {
                    if ($imageItem == null || empty($imageItem)) {
                        return ResponseUtils::json([
                            'code' => Response::HTTP_BAD_REQUEST,
                            'success' => false,
                            'msg_code' => MsgCode::INVALID_IMAGES[0],
                            'msg' => MsgCode::INVALID_IMAGES[1],
                        ]);
                    }
                }
            }
        }

        try {
            Carbon::parse($request->date_payment);
        } catch (Exception $ex) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_DATETIME[0],
                'msg' => MsgCode::INVALID_DATETIME[1]
            ]);
        }

        $billExistedData = $billExisted->first();

        // if (!empty($servicesRequest)) {
        //     if (!is_array($servicesRequest)) {
        //         return ResponseUtils::json([
        //             'code' => 404,
        //             'success' => false,
        //             'msg_code' => MsgCode::INVALID_LIST_SERVICE[0],
        //             'msg' => MsgCode::INVALID_LIST_SERVICE[1]
        //         ]);
        //     }
        // }
        if (
            $request->status != StatusBillDefineCode::CANCEL_BY_RENTER &&
            $request->status != StatusBillDefineCode::WAIT_FOR_CONFIRM
        ) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_BILL_STATUS[0],
                'msg' => MsgCode::INVALID_BILL_STATUS[1]
            ]);
        }

        // check status old bill
        if ($billExistedData->status == StatusBillDefineCode::COMPLETED) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::THE_BILL_HAS_PAID[0],
                'msg' => MsgCode::THE_BILL_HAS_PAID[1]
            ]);
        }
        if ($billExistedData->status == StatusBillDefineCode::CANCEL_BY_HOST) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::THE_BILL_HAS_CANCEL[0],
                'msg' => MsgCode::THE_BILL_HAS_CANCEL[1]
            ]);
        }
        if ($billExistedData->status == StatusBillDefineCode::CANCEL_BY_RENTER) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::CANCEL_BY_RENTER[0],
                'msg' => MsgCode::CANCEL_BY_RENTER[1]
            ]);
        }

        $billExisted->update([
            'bills.status' => $request->status,
            'bills.note' => $request->note,
            'bills.date_payment' => $request->date_payment ?? Helper::getTimeNowDateTime(),
            "bills.images"  => json_encode($images),
        ]);

        // setup notifications
        $contractExist = DB::table('contracts')->where('id', $billExistedData->contract_id)->first();
        $motelExistaaa = DB::table('motels')->where('id', $contractExist->motel_id)->first();
        if ($request->status == StatusBillDefineCode::WAIT_FOR_CONFIRM) {

            $master_coins = null;
            $renter_coins = null;
            $commissionPer =  Settings::where('type', 'commission')->first();
            $golden_coin = \auth()->user()->golden_coin;
            $wallet = WalletTransaction::where("user_id", \auth()->user()->id)->first();
            if (\auth()->user()->is_host) {
                $master_coins = $golden_coin + $wallet->deposit_money * (100 - $commissionPer->code_value) / 100;
            } else {
                $renter_coins = $golden_coin - $wallet->deposit_money * 1;
            }


            NotificationUserJob::dispatch(
                $contractExist->user_id,
                "Hoá đơn đã được thanh toán",
                'Hóa đơn tháng ' . $billExistedData->content . ': ' . Helper::currency_money_format($billExistedData->total_final)  . ' của phòng ' . $motelExistaaa->motel_name . ' đã được thanh toán bởi người thuê ' . $request->user->name,
                TypeFCM::BILL_HAS_PAID_WAIT_HOST_CONFIRM,
                NotiUserDefineCode::USER_IS_HOST,
                $billExistedData->id,
            );
        } else if ($request->status == StatusBillDefineCode::CANCEL_BY_RENTER) {
            NotificationUserJob::dispatch(
                $contractExist->user_id,
                "Hoá đơn đã bị hủy",
                'Hóa đơn tháng ' . $billExistedData->content . ': ' . Helper::currency_money_format($billExistedData->total_final) . ' của phòng ' . $motelExistaaa->motel_name . ' đã bị hủy bởi người thuê ' . $request->user->name,
                TypeFCM::BILL_CANCEL_BY_RENTER,
                NotiUserDefineCode::USER_IS_HOST,
                $billExistedData->id,
            );
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $billExisted->first(),
            'master_coin' =>  $master_coins,
            'renter_coins' => $renter_coins,
        ]);
    }


    /*
     * 
     * Danh sách hóa đơn 
     * 
     * @queryParam motel_id int require tìm hóa đơn theo mã phòng
     * @queryParam motel_name string số phòng
     * @queryParam status int mã trạng thái
     * @queryParam date_from datetime ngày bắt đầu tìm
     * @queryParam date_to datetime ngày bắt kết thúc
     * @queryParam descending boolean sắp xếp theo (default = false)
     * 
     */
    public function getAll(Request $request)
    {
        $motel_id = $request->motel_id;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;
        $carbon = DatetimeUtils::getNow();
        $date1 = null;
        $isExist = false;
        $date2 = null;
        $limit = $request->limit ?: 20;
        $sortBy = $request->sort_by ?? 'created_at';
        $descending =  filter_var($request->descending ?: true, FILTER_VALIDATE_BOOLEAN) ? 'desc' : 'asc';

        if (!ParamUtils::checkLimit($limit)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_BAD_REQUEST,
                'success' => false,
                'msg_code' => MsgCode::INVALID_LIMIT_REQUEST[0],
                'msg' => MsgCode::INVALID_LIMIT_REQUEST[1],
            ]);
        }

        if ($motel_id != null) {
            $isExist = !DB::table('motels')
                ->where([['id', $motel_id], ['status', StatusMotelDefineCode::MOTEL_HIRED]])
                ->exists();
            if (!$isExist) {
                return ResponseUtils::json([
                    'code' => Response::HTTP_NOT_FOUND,
                    'success' => false,
                    'msg_code' => MsgCode::NO_MOTEL_EXISTS[0],
                    'msg' => MsgCode::NO_MOTEL_EXISTS[1],
                ]);
            }
        }

        if ($dateFrom != null && $dateTo != null) {
            $date1 = $carbon->parse($dateFrom);
            $date2 = $carbon->parse($dateTo);

            $dateFrom = $date1->year . '-' . $date1->month . '-' . $date1->day . ' 00:00:00';
            $dateTo = $date2->year . '-' . $date2->month . '-' . $date2->day . ' 23:59:59';
        }
        // dd($request->user->phone_number);
        $listBill = Bill::sortByRelevance(true)
            ->join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
            ->where([
                ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL],
                ['user_contracts.renter_phone_number', $request->user->phone_number],
            ])
            ->when($request->status != null, function ($query) use ($request) {
                $query->where('bills.status', $request->status);
            })
            ->when($isExist, function ($query) use ($motel_id) {
                $query->where('user_contracts.motel_id', $motel_id);
            })
            ->when($dateFrom != null, function ($query) use ($dateFrom) {
                $query->where('bills.date_payment', '>=', $dateFrom);
            })
            ->when($dateTo != null, function ($query) use ($dateTo) {
                $query->where('bills.date_payment', '<=', $dateTo);
            })
            ->when(!empty($sortBy) && Bill::isColumnValid($sortBy), function ($query) use ($sortBy, $descending) {
                $query->orderBy($sortBy, $descending);
            })
            ->select('bills.*')
            ->distinct()
            ->paginate($limit);

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $listBill
        ]);
    }

    /**
     * 
     * Lấy 1 hóa đơn 
     * 
     * @urlParam bill_id int tìm hóa đơn theo mã 
     * 
     */
    public function getOne(Request $request, $id)
    {
        $billExisted = Bill::join('user_contracts', 'bills.contract_id', '=', 'user_contracts.contract_id')
            ->join('renters', 'user_contracts.renter_phone_number', '=', 'renters.phone_number')
            ->where([
                ['bills.id', $id],
                // ['renters.user_id', $request->user->id],
                ['bills.is_init', StatusBillDefineCode::NOT_INIT_BILL]
            ])
            ->select('bills.*')
            ->first();

        if (empty($billExisted)) {
            return ResponseUtils::json([
                'code' => Response::HTTP_NOT_FOUND,
                'success' => true,
                'msg_code' => MsgCode::NO_BILL_EXISTS[0],
                'msg' => MsgCode::NO_BILL_EXISTS[1]
            ]);
        }

        return ResponseUtils::json([
            'code' => Response::HTTP_OK,
            'success' => true,
            'msg_code' => MsgCode::SUCCESS[0],
            'msg' => MsgCode::SUCCESS[1],
            'data' => $billExisted
        ]);
    }
}
