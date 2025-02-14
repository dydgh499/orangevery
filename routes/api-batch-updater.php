<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateMchtController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateSalesController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdatePayModuleController;
use App\Http\Controllers\Manager\BatchUpdater\BatchUpdateNotiUrlController;
use App\Http\Controllers\Manager\Merchandise\RegularCreditCardController;

use App\Http\Controllers\Manager\Service\HolidayController;
use App\Http\Controllers\Manager\Service\MchtBlacklistController;

Route::prefix('salesforces/batch-updaters')->group(function() {
    Route::post('set-settle-tax-type', [BatchUpdateSalesController::class, 'setSettleTaxType']);
    Route::post('set-settle-cycle', [BatchUpdateSalesController::class, 'setSettleCycle']);
    Route::post('set-settle-day', [BatchUpdateSalesController::class, 'setSettleDay']);
    Route::post('set-is-able-modify-mcht', [BatchUpdateSalesController::class, 'setIsAbleModifyMcht']);
    Route::post('set-view-type', [BatchUpdateSalesController::class, 'setViewType']);
    Route::post('set-account-info', [BatchUpdateSalesController::class, 'setAccountInfo']);
    Route::post('set-note', [BatchUpdateSalesController::class, 'setNote']);
    Route::delete('remove', [BatchUpdateSalesController::class, 'batchRemove']);   
    Route::post('register', [BatchUpdateSalesController::class, 'register']);
});
    
Route::prefix('merchandises/batch-updaters')->group(function() {
    Route::post('set-noti-url', [BatchUpdateMchtController::class, 'setNotiUrl']);
    Route::post('set-merchant-status', [BatchUpdateMchtController::class, 'setMerchantStatus']);
    Route::post('set-custom-filter', [BatchUpdateMchtController::class, 'setCustomFilter']);
    Route::post('set-business-num', [BatchUpdateMchtController::class, 'setBusinessNum']);
    Route::post('set-resident-num', [BatchUpdateMchtController::class, 'setResidentNum']);
    Route::post('set-account-info', [BatchUpdateMchtController::class, 'setAccountInfo']);
    Route::post('set-show-fee', [BatchUpdateMchtController::class, 'setShowFee']);
    Route::delete('remove', [BatchUpdateMchtController::class, 'batchRemove']);

    Route::post('set-phone-auth-limit-count', [BatchUpdateMchtController::class, 'setPhoneAuthLimitCount']);
    Route::post('set-phone-auth-limit-time', [BatchUpdateMchtController::class, 'setPhoneAuthLimitTime']);
    Route::post('set-specified-time-disable-limit', [BatchUpdateMchtController::class, 'setSpecifiedTimeDisableLimit']);
    Route::post('set-specified-time-disable-time', [BatchUpdateMchtController::class, 'setSpecifiedTimeDisableTime']);
    Route::post('set-use-noti', [BatchUpdateMchtController::class, 'setUseNoti']);
    Route::post('register', [BatchUpdateMchtController::class, 'register']);
    Route::post('regular-credit-cards/register', [RegularCreditCardController::class, 'register']);
});
    
Route::prefix('merchandises/pay-modules/batch-updaters')->group(function() {       
    Route::post('set-payment-gateway', [BatchUpdatePayModuleController::class, 'setPaymentGateway']);
    Route::post('set-abnormal-trans-limit', [BatchUpdatePayModuleController::class, 'setAbnormalTransLimit']);
    Route::post('set-dupe-pay-count-validation', [BatchUpdatePayModuleController::class, 'setDupPayCountValidation']);
    Route::post('set-dupe-pay-least-validation', [BatchUpdatePayModuleController::class, 'setDupPayLeastValidation']);
    Route::post('set-settle-type', [BatchUpdatePayModuleController::class, 'setSettleType']);
    Route::post('set-settle-fee', [BatchUpdatePayModuleController::class, 'setSettleFee']);
    Route::post('set-mid', [BatchUpdatePayModuleController::class, 'setMid']);
    Route::post('set-tid', [BatchUpdatePayModuleController::class, 'setTid']);
    Route::post('set-pmid', [BatchUpdatePayModuleController::class, 'setPmid']);
    Route::post('set-api-key', [BatchUpdatePayModuleController::class, 'setApiKey']);
    Route::post('set-sub-key', [BatchUpdatePayModuleController::class, 'setSubKey']);
    Route::post('set-installment', [BatchUpdatePayModuleController::class, 'setInstallment']);
    Route::post('set-note', [BatchUpdatePayModuleController::class, 'setNote']);
    Route::post('set-pay-limit', [BatchUpdatePayModuleController::class, 'setPayLimit']);
    Route::post('set-pay-disable-time', [BatchUpdatePayModuleController::class, 'setForbiddenPayTime']);
    Route::post('set-use-realtime-deposit', [BatchUpdatePayModuleController::class, 'setUseRealtimeDeposit']);
    Route::post('set-withdraw-limit-type', [BatchUpdatePayModuleController::class, 'setWithdrawLimitType']);
    Route::post('set-withdraw-business-limit', [BatchUpdatePayModuleController::class, 'setWithdrawBusinessLimit']);
    Route::post('set-withdraw-holiday-limit', [BatchUpdatePayModuleController::class, 'setWithdrawHolidayLimit']);
    Route::post('set-fin-id', [BatchUpdatePayModuleController::class, 'setFinId']);
    Route::post('set-fin-trx-delay', [BatchUpdatePayModuleController::class, 'setFinTrxDelay']);
    Route::post('set-payment-term-min', [BatchUpdatePayModuleController::class, 'setPaymentTermMin']);
    Route::post('set-pay-window-secure-level', [BatchUpdatePayModuleController::class, 'setPayWindowSecureLevel']);
    Route::post('set-pay-window-extend-hour', [BatchUpdatePayModuleController::class, 'setPayWindowExtendHour']);
    Route::post('set-cxl-type', [BatchUpdatePayModuleController::class, 'setCxlType']);
    Route::post('set-pay-limit-type', [BatchUpdatePayModuleController::class, 'setPayLimitType']);
    Route::post('register', [BatchUpdatePayModuleController::class, 'register']);
    Route::delete('remove', [BatchUpdatePayModuleController::class, 'batchRemove']);   
});

Route::prefix('merchandises/noti-urls/batch-updaters')->group(function() {  
    Route::post('set-send-url', [BatchUpdateNotiUrlController::class, 'setSendUrl']);
    Route::post('set-noti-status', [BatchUpdateNotiUrlController::class, 'setNotiStatus']);
    Route::post('set-note', [BatchUpdateNotiUrlController::class, 'setNote']);
    Route::post('set-send-type', [BatchUpdateNotiUrlController::class, 'setSendType']);
    Route::delete('remove', [BatchUpdateNotiUrlController::class, 'batchRemove']);       
	Route::post('register', [BatchUpdateNotiUrlController::class, 'register']);
});
Route::prefix('services/mcht-blacklists/batch-updaters')->group(function() { 
    Route::post('register', [MchtBlacklistController::class, 'register']);
});
Route::prefix('services/holidays/batch-updaters')->group(function() { 
    Route::post('register', [HolidayController::class, 'register']);
});
