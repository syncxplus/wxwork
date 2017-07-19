window.jsApiList = [
    "onMenuShareAppMessage",
    "onMenuShareWechat",
    "startRecord",
    "stopRecord",
    "onVoiceRecordEnd",
    "playVoice",
    "pauseVoice",
    "stopVoice",
    "onVoicePlayEnd",
    "uploadVoice",
    "downloadVoice",
    "chooseImage",
    "previewImage",
    "uploadImage",
    "downloadImage",
    "getNetworkType",
    "openLocation",
    "getLocation",
    "hideOptionMenu",
    "showOptionMenu",
    "hideMenuItems",
    "showMenuItems",
    "hideAllNonBaseMenuItem",
    "showAllNonBaseMenuItem",
    "closeWindow",
    "scanQRCode"
];

$(function () {
    wx.config({
        debug:      true,
        appId:      "{{@jsConfig['appId']}}",
        timestamp:  "{{@jsConfig['timestamp']}}",
        nonceStr:   "{{@jsConfig['nonceStr']}}",
        signature:  "{{@jsConfig['signature']}}",
        jsApiList:  jsApiList
    });
    wx.error(wxAlert);
});
