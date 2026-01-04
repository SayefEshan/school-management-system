// Donut with legend
var _animatedDonutWithLegend = function (element, size, data) {
    if (typeof d3 == 'undefined') {
        console.warn('Warning - d3.min.js is not loaded.');
        return;
    }

    // Initialize chart only if element exsists in the DOM
    if (element) {

        // Main variables
        var d3Container = d3.select(element),
            distance = 2, // reserve 2px space for mouseover arc moving
            radius = (size / 2) - distance,
            sum = d3.sum(data, function (d) {
                return d.value;
            });


        // Create chart
        // ------------------------------

        // Add svg element
        var container = d3Container.append("svg");

        // Add SVG group
        var svg = container
            .attr("width", size)
            .attr("height", size)
            .append("g")
            .attr("transform", "translate(" + (size / 2) + "," + (size / 2) + ")");


        // Construct chart layout
        // ------------------------------

        // Pie
        var pie = d3.layout.pie()
            .sort(null)
            .startAngle(Math.PI)
            .endAngle(3 * Math.PI)
            .value(function (d) {
                return d.value;
            });

        // Arc
        var arc = d3.svg.arc()
            .outerRadius(radius)
            .innerRadius(radius / 1.5);


        //
        // Append chart elements
        //

        // Group chart elements
        var arcGroup = svg.selectAll(".d3-arc")
            .data(pie(data))
            .enter()
            .append("g")
            .attr("class", "d3-arc d3-slice-border")
            .style({
                'cursor': 'pointer'
            });

        // Append path
        var arcPath = arcGroup
            .append("path")
            .style("fill", function (d) {
                return d.data.color;
            });


        // Add interactions
        arcPath
            .on('mouseover', function (d, i) {

                // Transition on mouseover
                d3.select(this)
                    .transition()
                    .duration(500)
                    .ease('elastic')
                    .attr('transform', function (d) {
                        d.midAngle = ((d.endAngle - d.startAngle) / 2) + d.startAngle;
                        var x = Math.sin(d.midAngle) * distance;
                        var y = -Math.cos(d.midAngle) * distance;
                        return 'translate(' + x + ',' + y + ')';
                    });
            })
            .on('mouseout', function (d, i) {

                // Mouseout transition
                d3.select(this)
                    .transition()
                    .duration(500)
                    .ease('bounce')
                    .attr('transform', 'translate(0,0)');
            });

        // Animate chart on load
        arcPath
            .transition()
            .delay(function (d, i) {
                return i * 500;
            })
            .duration(500)
            .attrTween("d", function (d) {
                var interpolate = d3.interpolate(d.startAngle, d.endAngle);
                return function (t) {
                    d.endAngle = interpolate(t);
                    return arc(d);
                };
            });


        //
        // Append counter
        //

        // Append text
        svg
            .append('text')
            .attr('class', 'd3-text')
            .attr('text-anchor', 'middle')
            .attr('dy', 5)
            .style({
                'font-size': '17px',
                'font-weight': 500
            });

        // Animate text
        svg.select('text')
            .transition()
            .duration(1500)
            .tween("text", function (d) {
                var i = d3.interpolate(this.textContent, sum);
                return function (t) {
                    this.textContent = d3.format(",d")(Math.round(i(t)));
                };
            });


        //
        // Append legend
        //

        // Add element
        var legend = d3.select(element)
            .append('ul')
            .attr('class', 'chart-widget-legend')
            .selectAll('li').data(pie(data))
            .enter().append('li')
            .attr('data-slice', function (d, i) {
                return i;
            })
            .attr('style', function (d, i) {
                return 'border-bottom: 2px solid ' + d.data.color;
            })
            .text(function (d, i) {
                return d.data.status + ': ';
            });

        // Add value
        legend.append('span')
            .text(function (d, i) {
                return d.data.value;
            });
    }
};

// Simple sparklines
var _sparklinesWidget = function (element, chartType, qty, chartHeight, interpolation, duration, interval, color) {
    if (typeof d3 == 'undefined') {
        console.warn('Warning - d3.min.js is not loaded.');
        return;
    }

    // Initialize chart only if element exsists in the DOM
    if (element) {


        // Basic setup
        // ------------------------------

        // Define main variables
        var d3Container = d3.select(element),
            margin = {top: 0, right: 0, bottom: 0, left: 0},
            width = d3Container.node().getBoundingClientRect().width - margin.left - margin.right,
            height = chartHeight - margin.top - margin.bottom;


        // Generate random data (for demo only)
        var data = [];
        for (var i = 0; i < qty; i++) {
            data.push(Math.floor(Math.random() * qty) + 5);
        }


        // Construct scales
        // ------------------------------

        // Horizontal
        var x = d3.scale.linear().range([0, width]);

        // Vertical
        var y = d3.scale.linear().range([height - 5, 5]);


        // Set input domains
        // ------------------------------

        // Horizontal
        x.domain([1, qty - 3]);

        // Vertical
        y.domain([0, qty]);


        // Construct chart layout
        // ------------------------------

        // Line
        var line = d3.svg.line()
            .interpolate(interpolation)
            .x(function (d, i) {
                return x(i);
            })
            .y(function (d, i) {
                return y(d);
            });

        // Area
        var area = d3.svg.area()
            .interpolate(interpolation)
            .x(function (d, i) {
                return x(i);
            })
            .y0(height)
            .y1(function (d) {
                return y(d);
            });


        // Create SVG
        // ------------------------------

        // Container
        var container = d3Container.append('svg');

        // SVG element
        var svg = container
            .attr('width', width + margin.left + margin.right)
            .attr('height', height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


        // Add mask for animation
        // ------------------------------

        // Add clip path
        var clip = svg.append("defs")
            .append("clipPath")
            .attr('id', function (d, i) {
                return "load-clip-" + element.substring(1);
            });

        // Add clip shape
        var clips = clip.append("rect")
            .attr('class', 'load-clip')
            .attr("width", 0)
            .attr("height", height);

        // Animate mask
        clips
            .transition()
            .duration(1000)
            .ease('linear')
            .attr("width", width);


        //
        // Append chart elements
        //

        // Main path
        var path = svg.append("g")
            .attr("clip-path", function (d, i) {
                return "url(#load-clip-" + element.substring(1) + ")";
            })
            .append("path")
            .datum(data)
            .attr("transform", "translate(" + x(0) + ",0)");

        // Add path based on chart type
        if (chartType == "area") {
            path.attr("d", area).attr('class', 'd3-area').style("fill", color); // area
        } else {
            path.attr("d", line).attr("class", "d3-line d3-line-medium").style('stroke', color); // line
        }

        // Animate path
        path
            .style('opacity', 0)
            .transition()
            .duration(500)
            .style('opacity', 1);


        // Set update interval. For demo only
        // ------------------------------

        setInterval(function () {

            // push a new data point onto the back
            data.push(Math.floor(Math.random() * qty) + 5);

            // pop the old data point off the front
            data.shift();

            update();

        }, interval);


        // Update random data. For demo only
        // ------------------------------

        function update() {

            // Redraw the path and slide it to the left
            path
                .attr("transform", null)
                .transition()
                .duration(duration)
                .ease("linear")
                .attr("transform", "translate(" + x(0) + ",0)");

            // Update path type
            if (chartType == "area") {
                path.attr("d", area).attr('class', 'd3-area').style("fill", color);
            } else {
                path.attr("d", line).attr("class", "d3-line d3-line-medium").style('stroke', color);
            }
        }


        // Resize chart
        // ------------------------------

        // Call function on window resize
        window.addEventListener('resize', resizeSparklines);

        // Call function on sidebar width change
        var sidebarToggle = document.querySelector('.sidebar-control');
        sidebarToggle && sidebarToggle.addEventListener('click', resizeSparklines);

        // Resize function
        //
        // Since D3 doesn't support SVG resize by default,
        // we need to manually specify parts of the graph that need to
        // be updated on window resize
        function resizeSparklines() {

            // Layout variables
            width = d3Container.node().getBoundingClientRect().width - margin.left - margin.right;


            // Layout
            // -------------------------

            // Main svg width
            container.attr("width", width + margin.left + margin.right);

            // Width of appended group
            svg.attr("width", width + margin.left + margin.right);

            // Horizontal range
            x.range([0, width]);


            // Chart elements
            // -------------------------

            // Clip mask
            clips.attr("width", width);

            // Line
            svg.select(".d3-line").attr("d", line);

            // Area
            svg.select(".d3-area").attr("d", area);
        }
    }
};

// Animated progress with percentage count
var _progressPercentage = function (element, radius, border, foregroundColor, end) {
    if (typeof d3 == 'undefined') {
        console.warn('Warning - d3.min.js is not loaded.');
        return;
    }

    // Initialize chart only if element exsists in the DOM
    if (element) {


        // Basic setup
        // ------------------------------

        // Main variables
        var d3Container = d3.select(element),
            startPercent = 0,
            fontSize = 22,
            endPercent = end,
            twoPi = Math.PI * 2,
            formatPercent = d3.format('.0%'),
            boxSize = radius * 2;

        // Values count
        var count = Math.abs((endPercent - startPercent) / 0.01);

        // Values step
        var step = endPercent < startPercent ? -0.01 : 0.01;


        // Create chart
        // ------------------------------

        // Add SVG element
        var container = d3Container.append('svg');

        // Add SVG group
        var svg = container
            .attr('width', boxSize)
            .attr('height', boxSize)
            .append('g')
            .attr('transform', 'translate(' + radius + ',' + radius + ')');


        // Construct chart layout
        // ------------------------------

        // Arc
        var arc = d3.svg.arc()
            .startAngle(0)
            .innerRadius(radius)
            .outerRadius(radius - border)
            .cornerRadius(20);


        //
        // Append chart elements
        //

        // Paths
        // ------------------------------

        // Background path
        svg.append('path')
            .attr('class', 'd3-progress-background')
            .attr('d', arc.endAngle(twoPi))
            .style('fill', foregroundColor)
            .style('opacity', 0.1);

        // Foreground path
        var foreground = svg.append('path')
            .attr('class', 'd3-progress-foreground')
            .attr('filter', 'url(#blur)')
            .style({
                'fill': foregroundColor,
                'stroke': foregroundColor
            });

        // Front path
        var front = svg.append('path')
            .attr('class', 'd3-progress-front')
            .style({
                'fill': foregroundColor,
                'fill-opacity': 1
            });


        // Text
        // ------------------------------

        // Percentage text value
        var numberText = svg
            .append('text')
            .attr('dx', 0)
            .attr('dy', (fontSize / 2) - border)
            .style({
                'font-size': fontSize + 'px',
                'line-height': 1,
                'fill': foregroundColor,
                'text-anchor': 'middle'
            });


        // Animation
        // ------------------------------

        // Animate path
        function updateProgress(progress) {
            foreground.attr('d', arc.endAngle(twoPi * progress));
            front.attr('d', arc.endAngle(twoPi * progress));
            numberText.text(formatPercent(progress));
        }

        // Animate text
        var progress = startPercent;
        (function loops() {
            updateProgress(progress);
            if (count > 0) {
                count--;
                progress += step;
                setTimeout(loops, 10);
            }
        })();
    }
};
