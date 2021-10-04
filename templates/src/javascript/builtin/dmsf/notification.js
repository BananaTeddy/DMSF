/* jshint esversion:9 */

window.dmsf.Notification = class {
    constructor(options) {
        const {
            title,
            message,
            icon,
            lifeTime,
            handler,
            attachTo
        } = options;

        this.title = title || "title not set";
        this.message = message || "message not set";
        this.icon = icon || null;
        this.lifeTime = lifeTime || Infinity;
        this.initialLifeTime = this.lifeTime;
        this.attachTo = attachTo || document.body;
        this.handler = handler || null;
        this.id = 'note-' + window.md5(message + Date.now());

        this.addToDOM();
        this.updateTimer = setInterval(this.update, 100, this);
    }

    update(t) {
        t.lifeTime -= 100;
        let timerBar = document.querySelector(`#${t.id} .notification--timer`);
        let w = (t.lifeTime / t.initialLifeTime) * 100
        timerBar.style.width = w + "%";
        if (t.lifeTime <= 0) {
            document.getElementById(t.id).style.display = 'none';
            clearInterval(t.updateTimer);
        }
    }

    addToDOM() {
        let t = this;

        let notification    = document.createElement('div');
        let body            = document.createElement('div');
        let img             = document.createElement('img');
        let title           = document.createElement('h5');
        let message         = document.createElement('p');
        let close           = document.createElement('div');
        let closeSpan       = document.createElement('span');
        let timer           = document.createElement('div');

        notification.id = this.id;
        notification.classList.add('notification');
        body.classList.add('notification--body');
        close.classList.add('notification--close');
        timer.classList.add('notification--timer');
        close.appendChild(closeSpan);

        if (this.icon !== null) {
            img.src = this.icon;
            body.appendChild(img);
        }

        title.textContent = t.title;
        body.appendChild(title);
        message.textContent = t.message;
        body.appendChild(message);

        body.addEventListener('click', () => {
            if (this.handler !== null && typeof this.handler === 'function') {
                this.handler();
            }
            document.getElementById(this.id).style.display = 'none';
            this.lifeTime = 0;
        });
        close.addEventListener('click', () => {
            document.getElementById(this.id).style.display = 'none';
            this.lifeTime = 0;
        });
        notification.addEventListener('mouseenter', () => {
            clearInterval(this.updateTimer);
            this.lifeTime = this.initialLifeTime;
        })
        notification.addEventListener('mouseleave', () => {
            this.updateTimer = setInterval(this.update, 100, this);
        });

        notification.appendChild(body);
        notification.appendChild(timer);
        notification.appendChild(close);

        let note = this.attachTo.appendChild(notification);
        this.heightCorrection(note);
    }

    heightCorrection(notification) {
        let p = notification.querySelector('.notification--body p')
        let pHeight = p.getBoundingClientRect().height
        
        let h5 = notification.querySelector('.notification--body h5')
        let h5Height = h5.getBoundingClientRect().height
        
        let totalHeight = h5Height + pHeight + 16

        notification.style.height = totalHeight + "px"
        let body = notification.querySelector('.notification--body')
        body.style.height = totalHeight + "px"

        let close = notification.querySelector('.notification--close')
        close.style.height = totalHeight + "px"
    }

    removeFromDOM() {
        document.getElementById(this.id).remove();
    }
};


window.dmsf.NotificationManager = class {
    constructor(options = {}) {
        const {
            messageClearInterval,
            attachTo,
            styleRules = []
        } = options;
        this.messageClearInterval = messageClearInterval || 10000;

        let container = document.createElement('div');
        container.id = 'container--notifications';
        document.body.appendChild(container);
        this.attachTo = attachTo || document.getElementById(container.id);

        this.notifications = [];

        let style = document.createElement('style');
        this.styleRules = styleRules;
        document.head.appendChild(style);
        this.sheet = style.sheet;
        this.applyStyles();

        this.clearCycle = 0;
        this.clearTimer = setInterval(this.clearFinishedMessages, this.messageClearInterval, this);
    }

    addMessage(message) {
        let attach = {attachTo: this.attachTo};
        this.notifications.push(new window.dmsf.Notification({...message, ...attach}));
    }

    clearFinishedMessages(t) {
        let count = 0;
        t.clearCycle++;
        for (let [index, value] of t.notifications.entries()) {
            if (value.lifeTime <= 0) {
                t.notifications[index].removeFromDOM();
                t.notifications.splice(index, 1);
                count++;
            }
        }
        if (count > 0 ) {
            console.log(`${count} notification${count > 1 ? 's' : ''} removed in clear cycle ${t.clearCycle}`);
        }
    }

    clearMessages() {
        for (let [index, value] of this.notifications.entries()) {
            this.notifications[index].removeFromDOM();
            // remove from array
            this.notifications.splice(index, 1);
        }
    }

    applyStyles() {
        if (this.styleRules.length > 0) {
            for (var styleRule of this.styleRules) {
                this.sheet.insertRule(styleRule, this.sheet.rules.length);
            }
        }
    }
};


const notificationManager = new window.dmsf.NotificationManager({
    styleRules: [
        `#container--notifications {
            z-index: 1e6;
        }`
    ]
});