function clearCanvas(canvas) {
    var ctx=canvas.getContext('2d');
    ctx.clearRect(0,0,canvas.width,canvas.height);
    }
function drawCircleLine(cX,cY,radius,bgcolor,canvas) {
    var ctx=canvas.getContext('2d');
    ctx.beginPath();
    // gefuellter Kreis
    ctx.arc(cX,cY,radius,0,2*Math.PI,false);
    ctx.fillStyle=bgcolor;
    ctx.fill();
    // Verbindungslinie Uhrmittelpunkt zum Kreis
    ctx.lineWidth=5;
    ctx.strokeStyle=bgcolor;
    var RAD=0.5*canvas.width;
    ctx.moveTo(RAD,RAD);
    ctx.lineTo(cX,cY);
    ctx.stroke();
    }