<!DOCTYPE html>
<html>
<head>
    <title>Auto AEPS Capture</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

<h2>Auto AEPS Biometric</h2>

<p><b>Status:</b> <span id="status">Initializing...</span></p>

<label>Latitude:</label>
<input type="text" id="latitude"><br>

<label>Longitude:</label>
<input type="text" id="longitude"><br><br>

<textarea id="deviceInfo" rows="5" cols="80" placeholder="Device Info"></textarea><br><br>
<textarea id="pidData" rows="8" cols="80" placeholder="PID Data"></textarea><br><br>
<textarea id="biometricJson" rows="12" cols="80" placeholder="Biometric JSON"></textarea>

<script>

let RD_URL = null;

// ✅ AUTO LOAD
window.onload = async function () {
    getLocation();
    await autoDiscover();

    // 👉 अगर auto capture भी चाहिए तो uncomment करो
    // setTimeout(() => capture(), 1500);
};

// ✅ LOCATION
function getLocation(){
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition((pos)=>{
            $('#latitude').val(pos.coords.latitude);
            $('#longitude').val(pos.coords.longitude);
        });
    }
}

// ✅ AUTO DISCOVER
async function autoDiscover(){

    $('#status').text("🔍 Discovering device...");

    for(let port=11100; port<=11105; port++){
        try{
            let res = await fetch(`http://127.0.0.1:${port}`, {
                method: "RDSERVICE"
            });

            if(res.ok){
                let data = await res.text();

                $('#deviceInfo').val(data);

                RD_URL = `http://127.0.0.1:${port}`;

                $('#status').text("✅ Device Connected (Port " + port + ")");

                console.log("Device Found:", RD_URL);

                return;
            }

        }catch(e){
            console.log("Port failed:", port);
        }
    }

    $('#status').text("❌ Device Not Found");
}

// ✅ CAPTURE FUNCTION
function capture(){

    if(!RD_URL){
        alert("Device not connected");
        return;
    }

    $('#status').text("📡 Capturing Fingerprint...");

    let xml = `<?xml version="1.0"?>
    <PidOptions ver="1.0">
  <Opts 
    fCount="1"
    fType="2"
    format="0"
    pidVer="2.0"
    timeout="20000"
    wadh="YOUR_MANUAL_WADH_HERE"
    posh="UNKNOWN"
    env="P" />
</PidOptions>`;

    $.ajax({
        url: RD_URL + "/rd/capture",
        type: "CAPTURE",
        data: xml,
        contentType: "text/xml",
        success: function(res){

            $('#pidData').val(res);

            let json = xmlToJson($.parseXML(res));
            let bio = extractData(json);

            // ✅ JSON PRINT
            $('#biometricJson').val(JSON.stringify(bio, null, 4));

            $('#status').text("✅ Capture Success");

            console.log("Biometric JSON:", bio);
        },
        error: function(err){
            $('#status').text("❌ Capture Failed");
            console.log(err);
        }
    });
}


// ✅ XML → JSON
function xmlToJson(xml) {
    let obj = {};

    if (xml.nodeType === 1) {
        if (xml.attributes.length > 0) {
            obj["@attributes"] = {};
            for (let j = 0; j < xml.attributes.length; j++) {
                let attr = xml.attributes.item(j);
                obj["@attributes"][attr.nodeName] = attr.nodeValue;
            }
        }
    }

    if (xml.hasChildNodes()) {
        for (let i = 0; i < xml.childNodes.length; i++) {
            let item = xml.childNodes.item(i);
            let nodeName = item.nodeName;

            if (typeof(obj[nodeName]) === "undefined") {
                obj[nodeName] = xmlToJson(item);
            } else {
                if (!Array.isArray(obj[nodeName])) {
                    obj[nodeName] = [obj[nodeName]];
                }
                obj[nodeName].push(xmlToJson(item));
            }
        }
    }

    return obj;
}


// ✅ FINAL AEPS JSON
function extractData(res){

    let data = {
        dc: res?.PidData?.DeviceInfo?.["@attributes"]?.dc || "",
        ci: res?.PidData?.Skey?.["@attributes"]?.ci || "",
        hmac: res?.PidData?.Hmac?.["#text"] || "",
        dpId: res?.PidData?.DeviceInfo?.["@attributes"]?.dpId || "",
        mc: res?.PidData?.DeviceInfo?.["@attributes"]?.mc || "",
        mi: res?.PidData?.DeviceInfo?.["@attributes"]?.mi || "",
        rdsId: res?.PidData?.DeviceInfo?.["@attributes"]?.rdsId || "",
        sessionKey: res?.PidData?.Skey?.["#text"] || "",
        pidData: res?.PidData?.Data?.["#text"] || "",
        fCount: "1",
        fType: "2",
        iCount: "0",
        pCount: "0",
        errCode: res?.PidData?.Resp?.["@attributes"]?.errCode || "0",
        errInfo: res?.PidData?.Resp?.["@attributes"]?.errInfo || "",
        qScore: res?.PidData?.Resp?.["@attributes"]?.qScore || "",
        nmPoints: res?.PidData?.Resp?.["@attributes"]?.nmPoints || "",
        rdsVer: res?.PidData?.DeviceInfo?.["@attributes"]?.rdsVer || ""
    };

    console.log("Final AEPS Payload:", data);

    return data;
}

</script>

<br><br>
<button onclick="capture()">👉 Capture Finger</button>

</body>
</html>