<?php

namespace App\Constants;

class GlobalConst {
    const USER_PASS_RESEND_TIME_MINUTE = "1";
    const USER_VERIFY_RESEND_TIME_MINUTE = 1;

    const ACTIVE = true;
    const SUCCESS = true;
    const BANNED = false;
    const DEFAULT_TOKEN_EXP_SEC = 3600;

    const VERIFIED = 1;
    const APPROVED = 1;
    const PENDING = 2;
    const REJECTED = 3;
    const DEFAULT = 0;
    const UNVERIFIED = 0;

    const ACCOUNT_TYPE_BUSINESS = "business";

    const FIAT = "FIAT";
    const CRYPTO = "CRYPTO";


    const TRANSFER  = "transfer";
    const EXCHANGE  = "exchange";
    const ADD       = "add";
    const OUT       = "out";

    const AGENT     = "AGENT";
    const USER      = "USER";
    const MERCHANT  = "MERCHANT";

    const TRX_CASH_PICKUP                 = "Cash Pickup";
    const TRX_BANK_TRANSFER               = "Bank Transfer";
    // const TRX_MOBILE_MONEY                = "Mobile Money";
    const TRX_WALLET_TO_WALLET_TRANSFER   = "Wallet to Wallet Transfer";

    const SETUP_PAGE = 'SETUP_PAGE';
    const USEFUL_LINKS = 'USEFUL_LINKS';

    const LIVE = 'live';
    const SANDBOX = 'sandbox';

    const SENDER = 'SENDER';
    const RECEIVER = 'RECEIVER';
    const ENV_SANDBOX           = "sandbox";
    const ENV_PRODUCTION        = "production";
}
