<?php
/*
极速数据快递查询
*/
//电商加密私钥，快递鸟提供，注意保管，不要泄漏
defined('AppKey') or define('AppKey', 'f68ca2dc1acb8a55');
//请求url
defined('ReqURL') or define('ReqURL', 'http://api.jisuapi.com/express/query');

/*
提示：如果您需要的公司不在以下列表，请按以下方法自行添加或修改，快递公司名称区分大小写
后台配置路径应该跟ecshop一致;
快递公司编码下载地址：http://api.jisuapi.com/express/type?appkey=APPKEY

*/
switch ($getcom) {
    case "中通快递" :
        $postcom = "ZTO";
        break;
    case "圆通速递" :
        $postcom = "YTO";
        break;
    case "申通快递" :
        $postcom = "STO";
        break;
   case "韵达速递" :
        $postcom = "YUNDA";
        break;
    case "顺丰速运" :
        $postcom = "SFEXPRESS";
        break;
    case "天天快递" :
        $postcom = "TTKDEX";
        break;
   /*case "EMS" :
        $postcom = "EMS";
        break;*/
    case "邮政快递包裹" :
        $postcom = "CHINAPOST";
        break;
    case "Aramex" :
        $postcom = "ARAMEX";
        break;
    case "AAE" :
        $postcom = "AAEWEB";
        break;
    case "安捷" :
        $postcom = "ANJELEX";
        break;
    case "安信达" :
        $postcom = "ANXINDA";
        break;
    case "安能" :
        $postcom = "ANE";
        break;
    case "安能快递" :
        $postcom = "ANEEX";
        break;
    case "澳邮" :
        $postcom = "AUEXPRESS";
        break;
    case "方舟国际速递" :
        $postcom = "ARK";
        break;
    case "Aplusex" :
        $postcom = "APLUSEX";
        break;
    case "AOL澳通速递" :
        $postcom = "AOL";
        break;
    case "安迅物流" :
        $postcom = "ANXL";
        break;
    case "百福东方" :
        $postcom = "EES";
        break;
    case "黑狗" :
        $postcom = "BLACKDOG";
        break;
    case "百世快运" :
        $postcom = "BSKY";
        break;
    case "蓝天" :
        $postcom = "BLUESKY";
        break;
    case "程光" :
        $postcom = "FLYWAYEX";
        break;
    case "秦远物流" :
        $postcom = "CHINZ56";
        break;
    case "城市100" :
        $postcom = "CITY100";
        break;
    case "败欧洲" :
        $postcom = "EUROPE8";
        break;
    case "疯狂快递" :
        $postcom = "CRAZY";
        break;
    case "邦通国际" :
        $postcom = "COMEEXPRESS";
        break;
    case "德邦" :
        $postcom = "DEPPON";
        break;
    case "DHL" :
        $postcom = "DHL";
        break;
    case "大田" :
        $postcom = "DTW";
        break;
    case "D速" :
        $postcom = "DEXP";
        break;
    case "DPEX" :
        $postcom = "DPEX";
        break;
    case "东骏物流" :
        $postcom = "DJ56";
        break;
    case "大道物流" :
        $postcom = "DADAOEX";
        break;
    case "EMS" :
        $postcom = "EMS";
        break;
    case "EWE" :
        $postcom = "EWE";
        break;
    case "平安快递" :
        $postcom = "EFSPOST";
        break;
    case "卓志速运" :
        $postcom = "ESDEX";
        break;
    case "易达国际速递" :
        $postcom = "ETA";
        break;
    case "FedEx国际" :
        $postcom = "FEDEXIN";
        break;
    case "FedEx" :
        $postcom = "FEDEX";
        break;
    case "凤凰" :
        $postcom = "PHOENIXEXP";
        break;
    case "富腾达" :
        $postcom = "FTD";
        break;
    case "飞云快递" :
        $postcom = "FYEX";
        break;
    case "FDE" :
        $postcom = "FARDAR";
        break;
    case "能达" :
        $postcom = "ND56";
        break;
    case "共速达" :
        $postcom = "GSD";
        break;
    case "国通" :
        $postcom = "GTO";
        break;
    case "飞洋" :
        $postcom = "GCE";
        break;
    case "GTS快递" :
        $postcom = "GTO315";
        break;
    case "百世快递" :
        $postcom = "HTKY";
        break;
    case "华企" :
        $postcom = "HQKY";
        break;
    case "恒路" :
        $postcom = "HENGLU";
        break;
    case "鸿远" :
        $postcom = "HYE";
        break;
    case "锦程快递" :
        $postcom = "HREX";
        break;
    case "黄马甲" :
        $postcom = "HUANGMAJIA";
        break;
    case "上海航瑞货运" :
        $postcom = "HANGRUI";
        break;
    case "海联快递" :
        $postcom = "HLTOP";
        break;
    case "开心快递" :
        $postcom = "HAPPYLINK";
        break;
    case "嘉里物流" :
        $postcom = "KERRY";
        break;
    case "佳吉" :
        $postcom = "JIAJI";
        break;
    case "晋越" :
        $postcom = "PEWKEE";
        break;
    case "急先达" :
        $postcom = "JOUST";
        break;
    case "加运美" :
        $postcom = "TMS";
        break;
    case "佳怡" :
        $postcom = "JIAYI";
        break;
    case "京广" :
        $postcom = "KKE";
        break;
    case "京东" :
        $postcom = "JD";
        break;
    case "九曳" :
        $postcom = "JIUYESCM";
        break;
    case "加州猫速递" :
        $postcom = "JZM";
        break;
    case "佳惠尔" :
        $postcom = "JHEKD";
        break;
    case "跨越" :
        $postcom = "KYEXPRESS";
        break;
    case "快捷" :
        $postcom = "FASTEXPRESS";
        break;
    case "货运皇" :
        $postcom = "KINGFREIGHT";
        break;
    case "快服务" :
        $postcom = "KFW";
        break;
    case "龙邦" :
        $postcom = "LBEX";
        break;
    case "联昊通" :
        $postcom = "LTS";
        break;
    case "上海林道货运" :
        $postcom = "LDEXPRESS";
        break;
    case "龙枫国际速运" :
        $postcom = "LFEX";
        break;
    case "乐天速递" :
        $postcom = "LTEX";
        break;
    case "长风物流" :
        $postcom = "LONGVAST";
        break;
    case "民航" :
        $postcom = "CAE";
        break;
    case "优能物流" :
        $postcom = "MANTOO";
        break;
    case "新顺丰(NSF)" :
        $postcom = "NSF";
        break;
    case "配思航宇" :
        $postcom = "PEISI";
        break;
    case "PCA" :
        $postcom = "PCA";
        break;
    case "北极星快运" :
        $postcom = "POLARIS";
        break;
    case "平安达腾飞" :
        $postcom = "PADTF";
        break;
    case "全峰" :
        $postcom = "QFKD";
        break;
    case "全晨" :
        $postcom = "QCKD";
        break;
    case "全一" :
        $postcom = "APEX";
        break;
    case "启辰国际物流" :
        $postcom = "QICHEN";
        break;
    case "全速物流" :
        $postcom = "QUANSU";
        break;
    case "如风达" :
        $postcom = "RFD";
        break;
    case "日昱" :
        $postcom = "RYWL";
        break;
    case "瑞丰速递" :
        $postcom = "RFSD";
        break;
    case "日日顺" :
        $postcom = "RRSJK";
        break;
    case "顺丰" :
        $postcom = "SFEXPRESS";
        break;
    case "申通" :
        $postcom = "STO";
        break;
    case "盛辉" :
        $postcom = "SHENGHUI";
        break;
    case "盛丰" :
        $postcom = "SFWL";
        break;
    case "速尔" :
        $postcom = "SURE";
        break;
    case "三态" :
        $postcom = "SFC";
        break;
    case "苏宁" :
        $postcom = "SUNING";
        break;
    case "顺达快递" :
        $postcom = "SDEX";
        break;
    case "速通物流" :
        $postcom = "SUTONG";
        break;
    case "天越物流" :
        $postcom = "SURPASSGO";
        break;
    case "顺邦物流" :
        $postcom = "SHUNBANG";
        break;
    case "泰国138" :
        $postcom = "SD138";
        break;
    case "速配欧翼" :
        $postcom = "SUPEROZ";
        break;
    case "三象速递" :
        $postcom = "SXEXPRESS";
        break;
    case "速呈快递" :
        $postcom = "SUCHENG";
        break;
    case "德坤物流" :
        $postcom = "DEKUN";
        break;
    case "商桥" :
        $postcom = "SHANGQIAO56";
        break;
    case "四海" :
        $postcom = "SIHAIET";
        break;
    case "天天" :
        $postcom = "TTKDEX";
        break;
    case "天地华宇" :
        $postcom = "HOAU";
        break;
    case "TNT" :
        $postcom = "TNT";
        break;
    case "缔惠盛合" :
        $postcom = "TWKD56";
        break;
    case "天翔货运" :
        $postcom = "TIANXIANG";
        break;
    case "UPS" :
        $postcom = "UPS";
        break;
    case "uex国际物流" :
        $postcom = "UEX";
        break;
    case "优优速递" :
        $postcom = "UUEX";
        break;
    case "合众速递" :
        $postcom = "UCS";
        break;
    case "美国快递" :
        $postcom = "USEX";
        break;
    case "邮来速递" :
        $postcom = "ULEX";
        break;
    case "易境达" :
        $postcom = "USCBEXPRESS";
        break;
    case "万庚" :
        $postcom = "VANGEN";
        break;
    case "文捷航空" :
        $postcom = "GZWENJIE";
        break;
    case "万象" :
        $postcom = "EWINSHINE";
        break;
    case "万家物流" :
        $postcom = "WANJIA";
        break;
    case "新邦" :
        $postcom = "XBWL";
        break;
    case "信丰" :
        $postcom = "XFEXPRESS";
        break;
    case "西游寄" :
        $postcom = "XIYOU";
        break;
    case "先锋" :
        $postcom = "XIANFENGEX";
        break;
    case "圆通" :
        $postcom = "YTO";
        break;
    case "韵达" :
        $postcom = "YUNDA";
        break;
    case "邮政包裹" :
        $postcom = "CHINAPOST";
        break;
    case "运通" :
        $postcom = "YTEXPRESS";
        break;
    case "越丰" :
        $postcom = "YFEXPRESS";
        break;
    case "远成" :
        $postcom = "YCGWL";
        break;
    case "亚风" :
        $postcom = "BROADASIA";
        break;
    case "优速" :
        $postcom = "UC56";
        break;
    case "原飞航" :
        $postcom = "YFHEX";
        break;
    case "源安达" :
        $postcom = "YADEX";
        break;
    case "易通达" :
        $postcom = "ETD";
        break;
    case "易达通" :
        $postcom = "QEXPRESS";
        break;
    case "宜送" :
        $postcom = "YIEXPRESS";
        break;
    case "纵通速运" :
        $postcom = "YNZT";
        break;
    case "一号线国际速递" :
        $postcom = "YHX";
        break;
    case "中通" :
        $postcom = "ZTO";
        break;
    case "宅急送" :
        $postcom = "ZJS";
        break;
    case "中铁快运" :
        $postcom = "CRE";
        break;
    case "中国东方" :
        $postcom = "COE";
        break;
    case "中邮" :
        $postcom = "CNPL";
        break;
    case "中铁物流" :
        $postcom = "ZTKY";
        break;
    case "中通快运" :
        $postcom = "ZTO56";
        break;
    case "芝麻开门" :
        $postcom = "ZMKMEX";
        break;
    case "宅急便" :
        $postcom = "ZJB";
        break;
    case "增益速递" :
        $postcom = "ZENY";
        break;
    case "斑马物联" :
        $postcom = "ZEBRA";
        break;
    case "准实快运" :
        $postcom = "ZSKY";
        break;
    default:
        $postcom = '';
}