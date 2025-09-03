if (!window.shift4Initialised) {
    if (window.shift4JsLoaded) {
        initShift4();
        window.shift4Initialised = true;
    } else {
        document.addEventListener("shift4JsLoaded", function () {
            initShift4();
            window.shift4Initialised = true;
        });
    }
}