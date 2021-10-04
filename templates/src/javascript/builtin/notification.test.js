/* jshint esversion:9 */

var notificationManager = new window.dmsf.NotificationManager({
    styleRules: [
        `#container--notifications {
            z-index: 1e6;
        }`
    ]
});

notificationManager.addMessage({
    title: "Hello World",
    message: "It's so easy, easy, easy.",
    icon: "templates/media/images/shopware.png",
    handler: () => {
        window.location.href = "https://www.google.com/";
    }
});

notificationManager.addMessage({
    title: "New Ticket",
    message: "A new ticket was added. Priority: High. Click to reload.",
    icon: "templates/media/images/shopware.png",
    handler: () => {
        var doReload = window.confirm("Reload now?");
        if (doReload)
            window.location.reload();
    }
});
