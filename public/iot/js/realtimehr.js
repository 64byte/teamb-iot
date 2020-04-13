"use strict";

var limit = 30,
    duration = 1000,
    now = moment.parseZone(moment.tz(Date.now() - duration, "America/Los_Angeles").format()).valueOf()

var currentValue = 0;

var width = 500;
var height = 405;

var groups = {
    heartrate: {
        value: 0,
        color: 'red',
        data: d3.range(limit).map(function() {
            return 0
        })
    },
    rrinterval: {
        value: 0,
        color: 'green',
        data: d3.range(limit).map(function() {
            return 0
        })
    }
}

var x = d3.scaleTime()
    .domain([now - (limit - 2), now - duration])
    .range([0, width])

var y = d3.scaleLinear()
    .domain([0, 200])
    .range([height, 0])

var line = d3.line()
    .curve(d3.curveLinear)
    .x(function(d, i) {
        return x(now - (limit - 1 - i) * duration)
    })
    .y(function(d) {
        return y(d)
    })

var svg = d3.select('.graph').append('svg')
    .attr('class', 'chart')
    .attr('width', width)
    .attr('height', height + 50)

var xAxis = d3.axisBottom().scale(x);
var yAxis = d3.axisLeft().scale(y);

var axisX = svg.append('g')
    .attr('class', 'axis axis--x')
    .attr('transform', 'translate(20,' + height + ')')
    .call(xAxis)

var axisY = svg.append('g')
    .attr('class', 'axis axis--y')
    .attr("transform", "translate(" + 20 + "," + 0 + ")")
    .call(yAxis)
    .append("text")
    .attr("fill", "#000")
    .attr("transform", "rotate(-90)")
    .attr("y", 6)
    .attr("dy", "0.71em")
    .attr("text-anchor", "end")
    .attr("font-size", "14px")
    .text("Heart Rate");

var paths = svg.append('g')

for (var name in groups) {
    var group = groups[name]
    group.path = paths.append('path')
        .data([group.data])
        .attr('class', name + ' group')
        .style('stroke', group.color)
}

var elapsedTime = 0;
var startTime = new Date().getTime();

var avgHR = 0;
var avgRRInterval = 0;
function tick() {
    now = moment.parseZone(moment.tz(new Date(), "America/Los_Angeles").format()).valueOf();

    d3.csv("/api/users/" + user_id + "/hrdata?csv=true&limit=1&orderby=true", function (d) {
        d.hr = +d.hr;
        d.rri = (+d.rri)/1000;
        d.ts = new Date(d.timestamp);
        d.lat = +d.lat;
        d.lng = +d.lng;
        return d;
    }, function (err, data) {
        if (map == null)
            initialize();

        moveMarker(data[0]['lat'], data[0]['lng']);

        var dataTimeStamp = new Date(data[0]['ts']).getTime();

        if ((now.valueOf() - 5000) > dataTimeStamp) {
            location.reload();
        }

        d3.select('.current_hr')
            .html(data[0]['hr']);

        d3.select('.current_rr')
            .html(data[0]['rri']);

        d3.select('.current_time')
            .html(data[0]['ts'].toLocaleTimeString());

        d3.select('.current_loc')
            .html(data[0]['lat'].toFixed(3) + " / " + data[0]['lng'].toFixed(3));


        currentValue = data[0]['hr'];

        // Add new values
        for (var name in groups) {
            var group = groups[name]
            if (name === 'heartrate')
                group.data.push(data[0]['hr'])
            else if (name === 'rrinterval')
                group.data.push(data[0]['rri'])
            group.path.attr('d', line)
        }

        // Shift domain
        x.domain([now - (limit - 2) * duration, now - duration])

        axisX.transition()
            .duration(duration)
            .ease(d3.easeLinear,2)
            .call(xAxis);

        paths.attr('class', 'line')
            .attr('transform', null)
            .transition()
            .duration(duration)
            .ease(d3.easeLinear, 2)
            .attr('transform', 'translate(' + x(now - (limit - 1) * duration) + ')')
            .on('end', tick)

        // Remove oldest data point from each group
        for (var name in groups) {
            var group = groups[name]
            group.data.shift()
        }
    });
}

tick();