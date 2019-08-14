class Ajax {

    constructor(adDomClass, adDomData) {
        this.url = 'https://ads.alaatv.com/getAd';
        // Create a new XMLHttpRequest object
        this.xhr = new XMLHttpRequest();
        this.configureXMLHttpRequest(adDomClass, adDomData);
    }

    configureXMLHttpRequest(adDomClass, adDomData) {
        // Configure it: GET-request for the URL
        this.url += '?' + this.serialize(adDomData);
        // the parameter 'tags' is encoded
        this.xhr.open('GET', this.url, true);
        this.xhr.withCredentials = true;
        this.xhr.responseType = 'json';
        this.setXMLHttpRequestOnload(adDomClass, adDomData);
        this.setXMLHttpRequestOnerror();
    }

    sendXMLHttpRequest() {
        // Send the request over the network
        this.xhr.send();
    }

    setXMLHttpRequestOnload(adDomClass, adDomData) {
        // This will be called after the response is received
        this.xhr.onload = function () {
            if (this.status !== 200) { // analyze HTTP status of the response
                // alert(`Error ${xhr.status}: ${xhr.statusText}`); // e.g. 404: Not Found
            } else { // show the result
                // alert(`Done, got ${xhr.response.length} bytes`); // responseText is the server
                AlaaAds.loadAdDom(adDomClass, adDomData, this.response);
            }
        };
    }

    setXMLHttpRequestOnerror() {
        this.xhr.onerror = function () {
            // alert("Request failed");
        };
    }

    serialize(obj, prefix) {
        let str = [],
            p;
        for (p in obj) {
            if (obj.hasOwnProperty(p)) {
                let k = prefix ? prefix + "[" + p + "]" : p,
                    v = obj[p];
                str.push((v !== null && typeof v === "object") ?
                    this.serialize(v, k) :
                    encodeURIComponent(k) + "=" + encodeURIComponent(v));
            }
        }
        return str.join("&");
    }
}

let AlaaAds = function () {

    let attr_showFromOthers = 'sfo';

    function buildAllDoms() {
        let x = document.getElementsByClassName('AlaaAdDom');
        let i;
        let domLength = x.length;
        for (let i = 0; i < domLength; i++) {
            let dom = x[i],
                adDomClass = createAdDomClass(i),
                domAttrData = {};
            dom.classList.add(adDomClass),
                atts = dom.attributes,
                n = atts.length;
            for (let j = 0; j < n; j++){
                let res = atts[j].nodeName.match(/alaa-ad-.*/g);
                if(res !== null) {
                    domAttrData[atts[j].nodeName.replace('alaa-ad-', '')]=atts[j].value;
                }
            }

            let adDomData = {
                UUID: window.AlaaAdEngine.UUID,
                url: window.location.href
            };

            adDomData = Object.assign(adDomData, domAttrData);

            let ajaxload = new Ajax(adDomClass, adDomData);
            ajaxload.sendXMLHttpRequest();
        }
    }

    function createAdDomClass(adDomsCounter) {
        return 'AlaaAdDom' + adDomsCounter + '-' + (new Date()).getTime();
    }

    function createBlocks(data, adDomData) {
        let blocksHtml = '';
        let dataLength = data.length;
        for (let i = 0; i < dataLength; i++) {
            let block = data[i];
            blocksHtml += '<div class="AlaaAdDom-block">';
            blocksHtml += '<div class="AlaaAdDom-block-title"><h3>' + block.title + '</h3></div>';
            blocksHtml += createItems(block.data, adDomData);
            blocksHtml += '</div>';
        }

        return blocksHtml;
    }

    function createItems(data, adDomData) {
        let ItemsHtml = '<div class="AlaaAdDom-itemsWraper">';
        let dataLength = data.data.length;
        for (let i = 0; i < dataLength; i++) {
            ItemsHtml += createItem(data.data[i], adDomData);
        }
        ItemsHtml += '</div>';
        return ItemsHtml;
    }

    function createItem(data, adDomData) {
        let blockSize = '';
        if (typeof adDomData.size !== 'undefined') {
            blockSize = adDomData.size;
        }
        return '' +
            '            <div class="adsAlaatvRecomenderBlock-item '+blockSize+'">\n' +
            '                <div class="item-pic">\n' +
            '                    <a href="'+data.link+'">\n' +
            '                        <img src="'+data.image+'"  alt="'+data.name+'">\n' +
            '                    </a>\n' +
            '                </div>\n' +
            '                <div class="item-name">\n' +
            '                    <a href="'+data.link+'">\n' +
            '                        '+data.name+'\n' +
            '                    </a>\n' +
            '                </div>\n' +
            '            </div>';
    }

    function loadjscssfile(filename, fileType) {
        let fileRef;
        let now = new Date();
        filename += '?v=' + now.getFullYear().toString() + '0' + now.getMonth() + '0' + now.getDate() + '0' + now.getHours();
        if (fileType === "js"){ //if filename is a external JavaScript file
            fileRef = document.createElement('script');
            fileRef.setAttribute("type","text/javascript");
            fileRef.setAttribute("src", filename)
        }
        else if (fileType === "css"){ //if filename is an external CSS file
            fileRef = document.createElement("link");
            fileRef.setAttribute("rel", "stylesheet");
            fileRef.setAttribute("type", "text/css");
            fileRef.setAttribute("href", filename);
        }
        if (typeof fileRef!="undefined")
            document.getElementsByTagName("head")[0].appendChild(fileRef);
    }

    return {
        load: function () {
            buildAllDoms();
        },
        loadAdDom: function (adDomClass, adDomData, data) {
            let adDom = document.getElementsByClassName(adDomClass)[0];
            adDom.innerHTML = createBlocks(data, adDomData);
        },
        loadFile: function (filename, fileType) {
            loadjscssfile(filename, fileType)
        }
    };
}();

AlaaAds.load();
AlaaAds.loadFile('https://ads.alaatv.com/css/engine.css', 'css');
