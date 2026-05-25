<!DOCTYPE html>
<html>
<head>
    <title>Morpho RD Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>

<h2>Morpho RD Service Testing</h2>

<button onclick="discoverMorpho()">Discover Device</button>
<button onclick="captureMorpho()">Capture Finger</button>

<br><br>

<h3>Device Info</h3>
<textarea id="deviceInfo" rows="5" style="width:100%"></textarea>

<h3>PID Data (XML)</h3>
<textarea id="pidData" rows="7" style="width:100%"></textarea>

<h3>Biometric JSON</h3>
<textarea id="bioJson" rows="10" style="width:100%"></textarea>

<script>

let morphoPort = null;
let captureUrl = '';
let infoUrl = '';

/* ================= AUTO DISCOVER ================= */
async function discoverMorpho() {
    let ports = [11100,11101,11102,11103,11104,11105,8005];

    for (let port of ports) {
        try {
            let url = `http://127.0.0.1:${port}`;
            let res = await fetch(url, { method: "RDSERVICE" });

            if (res.ok) {
                let data = await res.text();

                if (data.includes("Morpho")) {
                    morphoPort = port;
                    $("#deviceInfo").val(data);

                    let xml = new DOMParser().parseFromString(data, "text/xml");

                    let interfaces = xml.getElementsByTagName("Interface");

                    for (let i = 0; i < interfaces.length; i++) {
                        let path = interfaces[i].getAttribute("path");

                        if (path === "/rd/capture") captureUrl = path;
                        if (path === "/rd/info") infoUrl = path;
                    }

                    alert("✅ Morpho Device Connected on port: " + port);
                    return;
                }
            }
        } catch (e) {
            console.log("Port fail:", port);
        }
    }

    alert("❌ Morpho Device Not Found");
}

/* ================= CAPTURE ================= */
function captureMorpho() {

    if (!morphoPort) {
        alert("पहले device discover करो");
        return;
    }

    let xml = `<?xml version="1.0"?>
    <PidOptions ver="1.0">
        <Opts fCount="1" fType="2" iCount="0" pCount="0"
        format="0" pidVer="2.0" timeout="20000"
        posh="UNKNOWN" env="P" />
    </PidOptions>`;

    let url = `http://127.0.0.1:${morphoPort}${captureUrl}`;

    $.ajax({
        type: "CAPTURE",
        url: url,
        data: xml,
        contentType: "text/xml",
        processData: false,

        success: function (data) {

            $("#pidData").val(data);

            let json = xmlToJson($.parseXML(data));
            $("#bioJson").val(JSON.stringify(json, null, 4));

            let bio = extractMorphoData(json);

            console.log("AEPS Payload:", bio);
            alert("✅ Capture Success");
        },

        error: function (err) {
            alert("❌ Capture Failed");
        }
    });
}

/* ================= EXTRACT DATA ================= */
function extractMorphoData(res) {

    return {
        dc: res?.PidData?.DeviceInfo?.["@attributes"]?.dc || "",
        ci: res?.PidData?.Skey?.["@attributes"]?.ci || "",
        hmac: res?.PidData?.Hmac?.["#text"] || "",
        dpId: res?.PidData?.DeviceInfo?.["@attributes"]?.dpId || "",
        mc: res?.PidData?.DeviceInfo?.["@attributes"]?.mc || "",
        mi: res?.PidData?.DeviceInfo?.["@attributes"]?.mi || "",
        rdsId: res?.PidData?.DeviceInfo?.["@attributes"]?.rdsId || "",
        sessionKey: res?.PidData?.Skey?.["#text"] || "",
        pidData: res?.PidData?.Data?.["#text"] || "",
        errCode: res?.PidData?.Resp?.["@attributes"]?.errCode || "0",
        qScore: res?.PidData?.Resp?.["@attributes"]?.qScore || "",
        nmPoints: res?.PidData?.Resp?.["@attributes"]?.nmPoints || "",
        srno: res?.PidData?.DeviceInfo?.additional_info?.Param?.[0]?.["@attributes"]?.value || ""
    };
}

/* ================= XML TO JSON ================= */
function xmlToJson(xml) {
    var obj = {};

    if (xml.nodeType === 1) {
        if (xml.attributes.length > 0) {
            obj["@attributes"] = {};
            for (var j = 0; j < xml.attributes.length; j++) {
                var attr = xml.attributes.item(j);
                obj["@attributes"][attr.nodeName] = attr.nodeValue;
            }
        }
    }

    if (xml.hasChildNodes()) {
        for (var i = 0; i < xml.childNodes.length; i++) {
            var item = xml.childNodes.item(i);
            var nodeName = item.nodeName;

            if (typeof (obj[nodeName]) === "undefined") {
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

</script>

</body>
</html>