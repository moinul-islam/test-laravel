<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>eINFO App</title>

    <!-- Open Graph for Facebook -->
    <meta property="og:title" content="কুতুবপুরের সোস্যাল মিডিয়া #eINFO App" />
    <meta property="og:description" content="কুতুবপুরের সোস্যাল মিডিয়া #eINFO App এ আমি আছি! আপনি আছেন তো?" />
    <meta property="og:image" content="https://einfo.site/assets/einfo-share.png" />
    <meta property="og:url" content="https://einfo.site/app" />
    <meta property="og:type" content="website" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body{
            font-family: system-ui, sans-serif;
            text-align:center;
            padding:40px 20px;
            background:#f9f9f9;
        }
        .box{
            background:#fff;
            padding:30px;
            border-radius:12px;
            max-width:420px;
            margin:auto;
            box-shadow:0 10px 30px rgba(0,0,0,.1);
        }
        img{
            width:100px;
            margin-bottom:20px;
        }
        h2{margin-bottom:10px;}
        p{color:#555;}
        a.btn{
            display:block;
            margin-top:15px;
            padding:12px;
            border-radius:8px;
            text-decoration:none;
            color:#fff;
            font-weight:600;
        }
        .android{background:#34a853;}
        .ios{background:#000;}
    </style>
</head>

<body>

<div class="box">
    <img src="https://einfo.site/assets/logo.png" alt="eINFO">
    <h2>eINFO App</h2>
    <p>আপনাকে অ্যাপে নিয়ে যাওয়া হচ্ছে…</p>

    <!-- Fallback buttons -->
    <a class="btn android" href="https://play.google.com/store/apps/details?id=com.einfo.site">
        Google Play Store
    </a>
    <a class="btn ios" href="https://apps.apple.com/us/app/einfo/id6748638531">
        App Store
    </a>
</div>

<script>
(function () {

    const androidStore = "https://play.google.com/store/apps/details?id=com.einfo.site";
    const iosStore = "https://apps.apple.com/us/app/einfo/id6748638531";

    /* ===== Deep Links (CHANGE if needed) ===== */
    const androidDeepLink = "intent://einfo.site#Intent;scheme=https;package=com.einfo.site;end";
    const iosDeepLink = "einfo://"; // তোমার iOS app scheme

    const ua = navigator.userAgent || navigator.vendor || window.opera;

    // iOS
    if (/iPhone|iPad|iPod/i.test(ua)) {
        window.location = iosDeepLink;
        setTimeout(() => {
            window.location = iosStore;
        }, 1200);
    }

    // Android
    else if (/Android/i.test(ua)) {
        window.location = androidDeepLink;
        setTimeout(() => {
            window.location = androidStore;
        }, 1200);
    }

})();
</script>

</body>
</html>
