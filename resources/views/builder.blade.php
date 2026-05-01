<!DOCTYPE html>
<html>
<head>
    <title>Vibe Code - Automation</title>
    <link rel="stylesheet" type="text/css" href="https://tamats.com/projects/litegraph/css/litegraph.css">
    <script type="text/javascript" src="https://tamats.com/projects/litegraph/build/litegraph.js"></script>
    <style>
        body { margin: 0; background: #222; color: white; font-family: sans-serif; }
        canvas { border: 1px solid #333; }
        .toolbar { padding: 10px; background: #333; }
        button { cursor: pointer; padding: 5px 15px; background: #007bff; color: white; border: none; border-radius: 3px; }
    </style>
</head>
<body>

<div class="toolbar">
    <button onclick="saveProject()">Save Workflow</button>
    <button onclick="runAutomation()" style="background: #28a745;">Run Automation</button>
</div>

<canvas id='mycanvas' width='1000' height='600'></canvas>

<script>
    var graph = new LGraph();
    var canvas = new LGraphCanvas("#mycanvas", graph);

    // --- تعريف نود الـ Log ---
    function NodeLog() {
        this.addInput("input", "string");
        this.addProperty("message", "Hello World");
        this.widget = this.addWidget("text", "Log", this.properties.message, (v) => { this.properties.message = v; });
    }
    NodeLog.title = "Log";
    NodeLog.prototype.onExecute = function() {
        var input = this.getInputData(0);
        console.log("LOG OUTPUT:", input || this.properties.message);
    };
    LiteGraph.registerNodeType("custom/log", NodeLog);

    // --- تعريف نود الـ Color ---
    function NodeColor() {
        this.addOutput("color", "string");
        this.addProperty("color", "#ff0000");
        this.widget = this.addWidget("color", "Pick Color", this.properties.color, (v) => { this.properties.color = v; });
    }
    NodeColor.title = "Color Picker";
    NodeColor.prototype.onExecute = function() {
        this.setOutputData(0, this.properties.color);
    };
    LiteGraph.registerNodeType("custom/color", NodeColor);

    // --- وظيفة الحفظ ---
    async function saveProject() {
        const data = graph.serialize();
        const response = await fetch('/api/save-workflow', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: 'My Automation', data: data })
        });
        alert("Saved Successfully!");
    }

    // --- وظيفة التشغيل ومتابعة الخرج ---
    function runAutomation() {
        console.clear();
        console.log("Starting Automation...");
        graph.runStep(); 
    }
</script>

</body>
</html>