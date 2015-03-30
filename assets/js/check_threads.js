var client = new ZeroClipboard($('.copy-button-rct'));
client.on("ready", function(readyEvent) {
    client.on("aftercopy", function(event) {
        alert("Copied text to clipboard");
    });
});
