/* jshint esversion: 6 */
class Chart {
    constructor(canvas, data, options) {
        this.canvas = canvas;
        this.data = data;
        this.options = options;
        this.boundHover = null;
    }

    draw() {
        const ctx = this.canvas.getContext('2d')
        ctx.clearRect(0, 0, this.canvas.width, this.canvas.height)
        this.dataPoints = [];
        switch (this.options.type) {
            case 'line':
                this.drawLineChart();
                break;
            case 'bar':
                this.drawBarChart();
                break;
            case 'pie':
                this.drawPieChart();
                break;
            case 'stackedBar':
                this.drawStackedBar();
                break;
            case 'spider':
                this.drawSpiderChart();
                break;
            default:
                console.error(`Invalid chart type ${typeof this.options.type} ${this.options.type}`);
                break;
        }
    }

    drawLineChart() {
        const ctx = this.canvas.getContext('2d');
        const fullCircle = 2 * Math.PI;
        const padding = this.options.chart.padding || 10;
        const radius = this.options.chart.radius || 5;
        const xDiff = Math.floor((this.canvas.width - padding * 2 - radius * 3 - this.canvas.width * 0.2) / (this.data.length -1));
        const offSetY = this.canvas.height * 0.8 - padding - radius;
        const useableHeight = this.canvas.height * 0.6 - 2 * padding - 2 * radius;
        const maxValue = this.options.chart.maxValue || Math.max(...this.data.map(i => i.value));
        const xStart = Math.floor(padding + radius * 1.5 + this.canvas.width * 0.1);
        const renderXLabels = typeof this.options.chart.renderXLabels === 'undefined' ? true : this.options.chart.renderXLabels;
        let x = xStart;
        let y = offSetY - this.data[0].value/maxValue * useableHeight;

        let divisor = 10;
        while (maxValue / divisor > 10) {
            divisor *= 10;
        }

        let Path = new Path2D();

        ctx.strokeStyle = 'black';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(x, this.canvas.height * 0.15);
        ctx.lineTo(x, offSetY);
        ctx.lineTo(this.canvas.width - x * 0.85, offSetY);
        ctx.stroke();

        ctx.beginPath();
        ctx.strokeStyle = 'rgba(32, 32, 32, 0.8)';
        ctx.fillStyle = 'black';
        ctx.font = '12px Calibri';
        ctx.setLineDash([5, 2]);
        var horizontalLines = Math.floor(maxValue / divisor);
        for (var i = 1; i <= horizontalLines; i++) {
            let hY = Math.floor(offSetY - useableHeight / maxValue * divisor * i);
            ctx.moveTo(x, hY);
            ctx.lineTo(this.canvas.width - x * 0.85, hY);
            ctx.fillText(divisor * i, x - ctx.measureText(divisor * i).width * 1.5, hY);
        }
        ctx.stroke();
        ctx.setLineDash([]);

        ctx.strokeStyle = this.options.colors[0];
        ctx.lineWidth = 2;

        ctx.beginPath();
        for (let data of this.data) {
            y = Math.floor(offSetY - data.value / maxValue * useableHeight - radius * 0.5);
            ctx.lineTo(x, y);
            Path.lineTo(x, y);
            x += xDiff;
        }
        ctx.stroke();

        Path.lineTo(x -xDiff, offSetY);
        Path.lineTo(xStart, offSetY);
        Path.lineTo(xStart, Math.floor(offSetY - this.data[0].value / maxValue * useableHeight - radius * 0.5));
        ctx.fillStyle = "rgba(100, 149, 237, 0.4)";
        ctx.fill(Path);

        x = xStart;
        ctx.fillStyle = 'white';
        for (let data of this.data) {
            ctx.beginPath();
            y = Math.floor(offSetY - data.value / maxValue * useableHeight - radius * 0.5) ;
            ctx.arc(x, y, radius, 0, fullCircle);
            this.dataPoints.push({
                x: x,
                y: y,
                r: Math.floor(radius * 1.5) + 2,
                startAngle: 0,
                endAngle: fullCircle,
                value: data.value,
                label: data.label
            });
            x += xDiff;
            ctx.fill();
            ctx.stroke();

            if (renderXLabels) {
                ctx.save();
                ctx.textAlign = 'center';
                ctx.font = '12px Calibri';
                ctx.translate(x - xDiff, offSetY + ctx.measureText(data.label).width);
                ctx.rotate(300 * (Math.PI / 180));
                ctx.fillStyle = 'black';
                ctx.fillText(data.label, 0, 0);
                // ctx.lineWidth = 1;
                // ctx.strokeRect(0, 0, this.canvas.width, this.canvas.height);
                ctx.restore();
            }
        }

        ctx.fillStyle = 'black';
        ctx.font = 'bold 20pt Calibri';
        ctx.fillText(
            this.options.chart.labelX, this.canvas.width / 2 - ctx.measureText(this.options.chart.labelX).width / 2,
            this.canvas.height - ctx.measureText('M').width / 2
        );
        ctx.save();
        ctx.translate(this.canvas.width * 0.05, this.canvas.height / 2 + this.canvas.width / 2);
        ctx.rotate(270 * (Math.PI / 180));
        ctx.fillText(this.options.chart.labelY, this.canvas.width / 2 - ctx.measureText(this.options.chart.labelY).width / 2, 0);
        // ctx.strokeRect(0, 0, this.canvas.width, this.canvas.height);
        ctx.restore();

        if (this.boundHover === null) {
            this.makeHoverable();
        }
    }

    drawBarChart() {
        // set constants
        const ctx = this.canvas.getContext('2d');
        const padding = this.options.chart.padding || 10;
        const barWidth = Math.floor((this.canvas.width - padding * 2 - this.data.length * 2 - this.canvas.width * 0.2) / (this.data.length -1));
        const offSetY = this.canvas.height * 0.8 - padding;
        const useableHeight = this.canvas.height * 0.6 - 2 * padding;
        const maxValue = this.options.chart.maxValue || Math.max(...this.data.map(i => i.value));
        const xStart = Math.floor(padding + this.canvas.width * 0.1);
        const renderXLabels = typeof this.options.chart.renderXLabels === 'undefined' ? true : this.options.chart.renderXLabels;
        let x = xStart;
        let y = offSetY - this.data[0].value/maxValue * useableHeight;

        let divisor = 10;
        while (maxValue / divisor > 10) {
            divisor *= 10;
        }

        // draw perimeter of graph
        ctx.strokeStyle = 'black';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(x, this.canvas.height * 0.15);
        ctx.lineTo(x, offSetY);
        ctx.lineTo(this.canvas.width - x * 0.85, offSetY);
        ctx.stroke();

        // draw horizontal measurement lines
        ctx.beginPath();
        ctx.strokeStyle = 'rgba(32, 32, 32, 0.8)';
        ctx.fillStyle = 'black';
        ctx.font = '12px Calibri';
        ctx.setLineDash([5, 2]);
        var horizontalLines = Math.floor(maxValue / divisor);
        for (var i = 1; i <= horizontalLines; i++) {
            let hY = Math.floor(offSetY - useableHeight / maxValue * divisor * i);
            ctx.moveTo(x, hY);
            ctx.lineTo(this.canvas.width - x * 0.85, hY);
            ctx.fillText(divisor * i, x - ctx.measureText(divisor * i).width * 1.5, hY);
        }
        ctx.stroke();
        ctx.setLineDash([]);

        ctx.fillStyle = this.options.colors[0];

        // draw bars
        x = xStart;
        for (let data of this.data) {
            let barHeight = Math.floor(data.value / maxValue * useableHeight);
            ctx.beginPath();
            ctx.rect(x, offSetY, barWidth, -barHeight);
            this.dataPoints.push({
                x: x,
                y: offSetY,
                w: barWidth,
                h: barHeight,
                value: data.value,
                label: data.label
            });
            x += barWidth + 2;
            ctx.fill();

            // draw small x labels
            if (renderXLabels) {
                ctx.save();
                ctx.textAlign = 'center';
                ctx.font = '12px Calibri';
                ctx.translate(x - barWidth, offSetY + ctx.measureText(data.label).width);
                ctx.rotate(300 * (Math.PI / 180));
                ctx.fillStyle = 'black';
                ctx.fillText(data.label, 0, 0);
                // ctx.lineWidth = 1;
                // ctx.strokeRect(0, 0, this.canvas.width, this.canvas.height);
                ctx.restore();
            }
        }


        ctx.fillStyle = 'black';
        ctx.font = 'bold 20pt Calibri';
        ctx.fillText(
            this.options.chart.labelX, this.canvas.width / 2 - ctx.measureText(this.options.chart.labelX).width / 2,
            this.canvas.height - ctx.measureText('M').width / 2
        );
        ctx.save();
        ctx.translate(this.canvas.width * 0.05, this.canvas.height / 2 + this.canvas.width / 2);
        ctx.rotate(270 * (Math.PI / 180));
        ctx.fillText(this.options.chart.labelY, this.canvas.width / 2 - ctx.measureText(this.options.chart.labelY).width / 2, 0);
        // ctx.strokeRect(0, 0, this.canvas.width, this.canvas.height);
        ctx.restore();

        if (this.boundHover === null) {
            this.makeHoverable();
        }
    }

    drawPieChart() {
        const ctx = this.canvas.getContext('2d');
        const padding = this.options.chart.padding || 10;
        const useableHeight = this.canvas.height * 0.5 - 2 * padding;
        const maxValue = Math.max(...this.data.map(i => i.value));
        const total = this.data.map(i => i.value).reduce((acc, cur) => acc + cur);
        let x = this.canvas.width / 2;
        let y = this.canvas.height / 2;
        let startAngle = 0;

        let i = 0;
        for (let data of this.data) {
            let radius = (data.value / maxValue) * useableHeight;
            radius = useableHeight;
            let endAngle = (data.value / total * 360) * (Math.PI / 180);
            ctx.fillStyle = this.options.colors[i % this.options.colors.length];

            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.arc(x, y, radius, startAngle, startAngle + endAngle);
            ctx.lineTo(x, y);
            this.dataPoints.push({
                x: x,
                y: y,
                r: radius,
                startAngle: startAngle,
                endAngle: endAngle,
                value: data.value,
                label: data.label
            });
            ctx.fill();
            ctx.stroke();
            startAngle += endAngle;
            i++;
        }

        if (this.boundHover === null) {
            this.makeHoverable();
        }
    }

    drawSpiderChart() {
        const ctx = this.canvas.getContext('2d');
        const padding = this.options.chart.padding || 10;
        const useableHeight = (this.canvas.height * 0.5) - (padding * 2);
        const maxValue = this.options.chart.maxValue || 5;
        let x = this.canvas.width * 0.5;
        let y = this.canvas.height * 0.5;
        let startAngle = 0;

        if (this.data.length < 3) {
            console.error("Spider Chart needs at least 3 datasets");
            return;
        }
        let angleSteps = 360 / this.data.length;

        ctx.save();
        ctx.translate(this.canvas.height * .15, this.canvas.width * .85);
        ctx.rotate(270 * (Math.PI / 180));
        ctx.strokeStyle = 'black';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(
            x + useableHeight * Math.cos(0),
            y + useableHeight * Math.sin(0)
        );
        for (var it = 0; it <= this.data.length; it++) {
            ctx.lineTo(
                x + (useableHeight * Math.cos(startAngle * (Math.PI / 180))),
                y + (useableHeight * Math.sin(startAngle * (Math.PI / 180)))
            );
            startAngle += angleSteps;
        }
        ctx.stroke();

        ctx.strokeStyle = 'rgba(64, 64, 64, 0.9)';
        ctx.linewidth = 2;
        ctx.setLineDash([3, 5]);
        for (var v = 1; v < maxValue; v++) {
            let radius = (v / maxValue) * useableHeight;
            startAngle = 0;
            ctx.beginPath();
            ctx.moveTo(
                x + radius * Math.cos(startAngle),
                y + radius * Math.sin(startAngle)
            );
            for (var it = 0; it <= this.data.length; it++) {
                ctx.lineTo(
                    x + radius * Math.cos(startAngle * (Math.PI / 180)),
                    y + radius * Math.sin(startAngle * (Math.PI / 180))
                );
                startAngle += angleSteps;
            }
            ctx.stroke();
        }
        ctx.setLineDash([]);


        startAngle = 0;
        ctx.fillStyle = "rgba(0, 255, 127, 0.4)";
        ctx.strokeStyle = "rgb(0, 255, 127)";
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(
            x + ((this.data[0].value / maxValue) * useableHeight) * Math.cos(startAngle * (Math.PI / 180)),
            y + ((this.data[0].value / maxValue) * useableHeight) * Math.sin(startAngle * (Math.PI / 180))
        );
        for (let data of this.data) {
            let radius = (data.value / maxValue) * useableHeight;
            ctx.lineTo(
                x + radius * Math.cos(startAngle * (Math.PI / 180)),
                y + radius * Math.sin(startAngle * (Math.PI / 180))
            );
            startAngle += angleSteps;
        }
        ctx.lineTo(
            x + ((this.data[0].value / maxValue) * useableHeight) * Math.cos(startAngle * (Math.PI / 180)),
            y + ((this.data[0].value / maxValue) * useableHeight) * Math.sin(startAngle * (Math.PI / 180))
        );
        ctx.stroke();
        ctx.fill();
        ctx.restore();

        return;
    }

    getColorFromRgba(i) {
        return `rgba(${this.options.colors[i].r}, ${this.options.colors[i].g}, ${this.options.colors[i].b}, ${this.options.colors[i].a}`;
    }

    drawStackedBar() {
        const ctx = this.canvas.getContext('2d');
        const padding = this.options.chart.padding || 10;
        const barWidth = Math.floor((this.canvas.width - padding * 2 - this.data.length * 2 - this.canvas.width * 0.2) / (this.data.length));
        const offSetY = this.canvas.height * 0.8 - padding;
        const useableHeight = this.canvas.height * 0.6 - 2 * padding;
        var totalValues = [];
        for (var values of this.data.map(i => i.values)) {
            totalValues.push(values.reduce((acc, cur) => acc + cur));
        }
        const maxValue = Math.max(...totalValues);
        const xStart = Math.floor(padding + this.canvas.width * 0.1);
        let x = xStart;
        let y = offSetY;
        const darkMode = this.options.chart.darkMode || false;


        let divisor = 10;
        while (maxValue / divisor > 10) {
            divisor *= 10;
        }

        if (! darkMode) {
            ctx.strokeStyle = 'black';
        } else {
            ctx.strokeStyle = 'whitesmoke';
        }
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(x, this.canvas.height * 0.15);
        ctx.lineTo(x, offSetY);
        ctx.lineTo(this.canvas.width - x * 0.85, offSetY);
        ctx.stroke();

        ctx.beginPath();
        if (! darkMode) {
            ctx.strokeStyle = 'rgba(32, 32, 32, 0.8)';
            ctx.fillStyle = 'black';
        } else {
            ctx.strokeStyle = '#f5f5f5cc';
            ctx.fillStyle = 'whitesmoke';
        }
        
        ctx.font = '12px Calibri';
        ctx.setLineDash([5, 2]);
        var horizontalLines = Math.floor(maxValue / divisor);
        for (var i = 1; i <= horizontalLines; i++) {
            let hY = Math.floor(offSetY - useableHeight / maxValue * divisor * i);
            ctx.moveTo(x, hY);
            ctx.lineTo(this.canvas.width - x * 0.85, hY);
            ctx.fillText(divisor * i, x - ctx.measureText(divisor * i).width * 1.5, hY);
        }
        ctx.stroke();
        ctx.setLineDash([]);

        ctx.fillStyle = this.options.colors[0];

        x = xStart;
        for (let data of this.data) {
            var barOffsetY = 0;
            var totalValue = data.values.reduce((acc, cur) => acc + cur);
            var o = {
                x: x,
                y: y,
                w: barWidth,
                values: [],
                label: data.label
            };

            for (var [i, value] of data.values.entries()) {
                ctx.beginPath();
                ctx.fillStyle = this.options.colors[i];
                let barHeight = Math.floor((value / totalValue) * (totalValue / maxValue) * useableHeight);
                ctx.rect(x, y + barOffsetY, barWidth, -barHeight);
                barOffsetY -= barHeight;
                ctx.fill();
                o.values.push(value);
            }
            o.h = -barOffsetY;

            this.dataPoints.push(o);

            x += barWidth + 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.font = '12px Calibri';
            ctx.translate(x - barWidth / 2, offSetY + ctx.measureText(data.label).width);
            ctx.rotate(300 * (Math.PI / 180));
            if (! darkMode) {
                ctx.fillStyle = 'black';
            } else {
                ctx.fillStyle = 'whitesmoke';
            }
            ctx.fillText(data.label, 0, 0);
            ctx.restore();
        }

        if (! darkMode) {
            ctx.fillStyle = 'black';
        } else {
            ctx.fillStyle = 'whitesmoke';
        }
        ctx.font = 'bold 20pt Calibri';
        ctx.fillText(
            this.options.chart.labelX, this.canvas.width / 2 - ctx.measureText(this.options.chart.labelX).width / 2,
            this.canvas.height - ctx.measureText('M').width / 2
        );
        ctx.save();
        ctx.translate(this.canvas.width * 0.05, this.canvas.height / 2 + this.canvas.width / 2);
        ctx.rotate(270 * (Math.PI / 180));
        ctx.fillText(this.options.chart.labelY, this.canvas.width / 2 - ctx.measureText(this.options.chart.labelY).width / 2, 0);
        ctx.restore();

        if (this.boundHover === null) {
            this.makeHoverable();
        }
    }

    drawTooltip(x, y, data) {
        const ctx = this.canvas.getContext('2d');
        const padding = this.options.chart.padding || 10;
        let textSizes = [];
        let i = 0;

        x += 20
        y += 20

        let fontSize = 20;
        ctx.font = fontSize + 'px Calibri';
        for (var text of data) {
            textSizes.push(ctx.measureText(text).width);
        }
        let width = Math.max(...textSizes) + padding * 2;
        let height = data.length * fontSize + padding * 2;
        if (x + width > this.canvas.width) x -= width;
        if (y + height > this.canvas.height) y -= height;
        ctx.fillStyle = 'rgba(0, 0, 0, 0.8)';
        ctx.fillRect(x, y, width, height);
        ctx.fillStyle = 'white';
        while (text = data[i++]) {
            ctx.fillText(
                text,
                x + padding,
                y + i * fontSize + padding - fontSize / 4
            );
        }
    }

    hover(e) {
        let canvas = e.target;
        let rect = canvas.getBoundingClientRect();
        let x = e.clientX - rect.left;
        let y = e.clientY - rect.top;
        let i = 0;
        let ctx = canvas.getContext('2d');
        let dP;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        this.draw();
        while (dP = this.dataPoints[i++]) {
            ctx.beginPath();
            switch (this.options.type) {
                case 'line':
                    ctx.arc(dP.x, dP.y, dP.r, dP.startAngle, dP.endAngle);
                    break;
                case 'pie':
                    ctx.moveTo(dP.x, dP.y);
                    ctx.arc(dP.x, dP.y, dP.r, dP.startAngle, dP.startAngle + dP.endAngle);
                    ctx.lineTo(dP.x, dP.y);
                    break;
                default:
                    ctx.rect(dP.x, dP.y, dP.w, -dP.h);
                    break;
            }

            if (ctx.isPointInPath(x, y)) {
                if (this.options.type == 'stackedBar') {
                    let tooltip = [
                        this.options.chart.labels[0],
                        dP.label
                    ];
                    for (var [j, value] of dP.values.entries()) {
                        tooltip.push(this.options.chart.labels[j+1] + ": " + value);
                    }
                    this.drawTooltip(x, y, tooltip);
                } else {
                    this.drawTooltip(x, y, [dP.label, dP.value]);
                }
            }
            ctx.closePath();
        }
    }

    makeHoverable() {
        this.boundHover = this.hover.bind(this);
        this.canvas.addEventListener('mousemove', this.boundHover);
    }

    removeHover() {
        this.canvas.removeEventListener('mousemove', this.boundHover);
        this.boundHover = null;
    }
}
