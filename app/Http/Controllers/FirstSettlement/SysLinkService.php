<?php
namespace App\Http\Controllers\FirstSettlement;

class SysLinkService
{
    static private $headers = [
        'Content-Type' => 'application/json; charset=utf-8',
        'Authorization' => 'sk_de0c1dbee096ee9a54d9341cd60fc',
    ];

    static public function create($mcht)
    {
        $params = [
            "id"          => $mcht['id'],
            "status"      => "active",
            "brnType"     => "사업자",
            "brn"         => $mcht['business_num'],
            "nick"        => $mcht['mcht_name'],
            "name"        => $mcht['nick_name'],
            "bizType"     => $mcht['sector'],
            "phoneNo"     => $mcht['phone_num'],
            "telNo"       => $mcht['contact_num'],
            "email"       => "",
            "address"     => $mcht['addr'],
            "accnt" => [
                "bankCd"    => $mcht['acct_bank_code'],
                "account"   => $mcht['acct_num'],
                "holder"    => $mcht['acct_name']
            ]
        ];
        $res = post('https://api.syslink.kr/v1/sapp/create', $params, self::$headers);
        return $res['body'];
    }

    static public function update($mcht)
    {
        $params = [
            "status"      => "active",
            "brnType"     => "사업자",
            "brn"         => $mcht['business_num'],
            "nick"        => $mcht['mcht_name'],
            "name"        => $mcht['nick_name'],
            "bizType"     => $mcht['sector'],
            "phoneNo"     => $mcht['phone_num'],
            "telNo"       => $mcht['contact_num'],
            "email"       => "",
            "address"     => $mcht['addr'],
        ];
        $res = post('https://api.syslink.kr/v1/sapp/update/'.$mcht['id'], $params, self::$headers);

        $acct_params = [
            "bankCd"    => $mcht['acct_bank_code'],
            "account"   => $mcht['acct_num'],
            "holder"    => $mcht['acct_name']
        ];
        $res = post('https://api.syslink.kr/v1/accnt/update/'.$mcht['user_name'], $acct_params, self::$headers);
        return $res['body'];
    }

    static public function show($id)
    {
        $res = get('https://api.syslink.kr/'.$id, [], self::$headers);
        return $res['body'];
    }
}
