<!DOCTYPE html>
<html>
<head>
    <title>Vibe Code - Automation</title>
    <link rel="stylesheet" type="text/css" href="https://tamats.com/projects/litegraph/css/litegraph.css">
    <script type="text/javascript" src="https://tamats.com/projects/litegraph/build/litegraph.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { margin: 0; background: #1a1a1a; color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .toolbar { padding: 15px; background: #2d2d2d; display: flex; gap: 10px; border-bottom: 2px solid #3d3d3d; }
        button { padding: 10px 20px; cursor: pointer; border: none; border-radius: 5px; background: #4a4a4a; color: white; transition: 0.3s; }
        button:hover { background: #666; }
        .btn-save { background: #007bff; }
        .btn-run { background: #28a745; }
        canvas { display: block; background-image: radial-gradient(#333 1px, transparent 1px); background-size: 20px 20px; }
    </style>
</head>
<body>

<div class="toolbar">
    <button onclick="addNode('custom/color')">+ Add Color Node</button>
    <button onclick="addNode('custom/log')">+ Add Log Node</button>
    <button onclick="saveWorkflow()" class="btn-save">Save Project</button>
    <button onclick="runAutomation()" class="btn-run">Run Automation</button>
</div>

<canvas id='mycanvas' width='1400' height='800'></canvas>

<script>
    // 1. إعداد الـ Graph والـ Canvas
    var graph = new LGraph();
    var canvas = new LGraphCanvas("#mycanvas", graph);

    // --- تعريف نود Color ---
    function NodeColor() {
        this.addOutput("color_out", "string"); // مخرج من نوع نص (كود اللون)
        this.addProperty("selected_color", "#ff0000"); // القيمة الافتراضية المحفوظة
        
        // إضافة ويدجيت لاختيار اللون وحفظ قيمته فوراً
        this.addWidget("color", "Pick Color", this.properties.selected_color, (value) => {
            this.properties.selected_color = value;
            console.log("Color changed to:", value);
        });
    }
    NodeColor.title = "Color Picker";
    NodeColor.prototype.onExecute = function() {
        // إرسال اللون المختار للمخرج
        this.setOutputData(0, this.properties.selected_color);
    };
    LiteGraph.registerNodeType("custom/color", NodeColor);

    // --- تعريف نود Log ---
    function NodeLog() {
        this.addInput("data_in", "string"); // مدخل يستقبل نص
        this.addProperty("default_text", "No Data");
        this.widget = this.addWidget("text", "Default Log", this.properties.default_text, (v) => {
            this.properties.default_text = v;
        });
    }
    NodeLog.title = "Log Output";
    NodeLog.prototype.onExecute = function() {
        // قراءة البيانات من الوصلة (Input) أو استخدام النص الافتراضي
        let inputData = this.getInputData(0);
        let finalValue = inputData ? inputData : this.properties.default_text;
        
        console.log("%c LOG OUTPUT: " + finalValue, "color: " + (inputData ? inputData : "#fff") + "; font-weight: bold; font-size: 14px;");
    };
    LiteGraph.registerNodeType("custom/log", NodeLog);

    // --- وظائف التحكم ---

    // إضافة نود للمشهد
    function addNode(type) {
        var node = LiteGraph.createNode(type);
        node.pos = [100 + Math.random()*200, 100 + Math.random()*200];
        graph.add(node);
    }

    // تشغيل الاوتوميشن ومتابعة الخرج في الكونسول
    function runAutomation() {
        console.log("--- Running Automation ---");
        graph.runStep();
    }

    // حفظ المشروع كاملاً في قاعدة البيانات (Laravel)
    async function saveWorkflow() {
        const graphData = graph.serialize(); // هذا يحول كل النودات، الألوان، والوصلات إلى JSON
        
        try {
            const response = await fetch('/api/save-workflow', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' // ضروري لـ Laravel
                },
                body: JSON.stringify({
                    name: "Automation Project",
                    data: graphData
                })
            });

            if (response.ok) {
                alert("تم حفظ المشروع والوصلات بنجاح!");
            } else {
                alert("حدث خطأ أثناء الحفظ");
            }
        } catch (error) {
            console.error("Error:", error);
        }
    }

    // تحميل آخر مشروع محفوظ (اختياري)
    async function loadWorkflow() {
        const response = await fetch('/api/workflows');
        const workflows = await response.json();
        if (workflows.length > 0) {
            graph.configure(workflows[workflows.length - 1].data);
        }
    }

    // تشغيل التحميل التلقائي عند فتح الصفحة
    // window.onload = loadWorkflow;

    async function saveWorkflow() {
    const graphData = graph.serialize(); 
    
    // إظهار البيانات في الكونسول للتأكد أن الـ Node تعمل
    console.log("تجهيز البيانات للحفظ:", graphData);

    try {
        const response = await fetch('/api/save-workflow', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // أضفنا هذا السطر للتأكد من تجاوز حماية لارافيل
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({
                name: "My Automation",
                data: graphData
            })
        });

        const result = await response.json();

        if (response.ok) {
            console.log("الرد من السيرفر:", result);
            alert("تم الحفظ بنجاح في قاعدة البيانات!");
        } else {
            console.error("خطأ من السيرفر:", result);
            alert("فشل الحفظ: " + (result.message || "خطأ غير معروف"));
        }
    } catch (error) {
        console.error("خطأ في الاتصال:", error);
        alert("لا يمكن الاتصال بالسيرفر، تأكد من تشغيل php artisan serve");
    }
}

// --- تعريف نود Color المحدثة ---
function NodeColor() {
    // 1. إضافة مخرج لإرسال اللون لنود أخرى
    this.addOutput("color_out", "string");

    // 2. تعريف خاصية تخزين اللون (هذه التي سيتم حفظها في قاعدة البيانات)
    this.properties = { 
        selected_color: "#3366ff" 
    };

    // 3. إضافة الويدجيت (لوحة الألوان)
    // عند تغيير اللون، سيتم تحديث this.properties.selected_color تلقائياً
    this.addWidget("color", "Choose Color", this.properties.selected_color, (value) => {
        this.properties.selected_color = value;
    }, { property: "selected_color" });

    // تحديد أبعاد النود لتناسب الويدجيت
    this.size = [180, 60];
}

NodeColor.title = "Color Picker";

// الدالة التي تعمل عند الضغط على Run
NodeColor.prototype.onExecute = function() {
    // إخراج القيمة المحفوظة في الـ properties
    this.setOutputData(0, this.properties.selected_color);
    
    // تغيير لون النود نفسها في المشهد ليعكس اللون المختار (لمسة جمالية)
    this.color = this.properties.selected_color;
};

LiteGraph.registerNodeType("custom/color", NodeColor);

// --- نود الـ Log لاستقبال اللون ---
function NodeLog() {
    // إضافة مدخل أول
    this.addInput("input", "string");
    
    this.properties = { manual_text: "Log result" };
    this.addWidget("text", "Message", this.properties.manual_text, (v) => {
        this.properties.manual_text = v;
    });

    // ميزة رائعة: تسمح بتوصيل أكثر من سلك بنفس المخرج (إذا أردت ذلك برمجياً)
    // لكن الأفضل في LiteGraph هو إضافة مدخلات متعددة
    this.size = [200, 80];
}

NodeLog.title = "Log (Multi-Link)";

NodeLog.prototype.onExecute = function() {
    // الحصول على البيانات من جميع المداخل الممكنة
    // سنمر على كل المداخل الموصلة ونجمع نتائجها
    let outputs = [];
    
    for (let i = 0; i < this.inputs.length; i++) {
        let data = this.getInputData(i);
        if (data !== undefined) {
            outputs.push(data);
        }
    }

    if (outputs.length > 0) {
        console.log("%c Multi-Log Output: ", "font-weight: bold; color: yellow;");
        outputs.forEach((val, index) => {
            console.log(`Input ${index + 1}: %c${val}`, `color: ${val}; font-weight: bold;`);
        });
    } else {
        console.log("Log (Manual):", this.properties.manual_text);
    }
};

// وظيفة لإضافة مدخل جديد تلقائياً عند الحاجة (إضافة لمسة احترافية)
NodeLog.prototype.onConnectionsChange = function(type, index, connected, link_info) {
    // إذا تم توصيل سلك بالمدخل الأخير، قم بإضافة مدخل جديد فارغ
    if (type == LiteGraph.INPUT && connected && index == this.inputs.length - 1) {
        this.addInput("input", "string");
    }
};

LiteGraph.registerNodeType("custom/log", NodeLog);
NodeLog.title = "Log Output";

NodeLog.prototype.onExecute = function() {
    // جلب البيانات من الوصلة إذا كانت موجودة
    let inputData = this.getInputData(0);
    
    // إذا كان هناك وصلة (مثل نود اللون) نأخذ قيمتها، وإلا نأخذ النص المكتوب يدوياً
    let finalOutput = inputData ? inputData : this.properties.manual_text;
    
    // طباعة النتيجة في الكونسول
    if (inputData) {
        // إذا كان المدخل لوناً، سنطبعه بشكل ملون في الكونسول
        console.log("%c Log (From Input): " + finalOutput, "color: " + finalOutput + "; font-weight: bold; border: 1px solid; padding: 2px;");
    } else {
        // طباعة النص العادي
        console.log("Log (Manual):", finalOutput);
    }
};

LiteGraph.registerNodeType("custom/log", NodeLog);
NodeLog.title = "Log";
NodeLog.prototype.onExecute = function() {
    let colorVal = this.getInputData(0);
    if (colorVal) {
        console.log("%c Received Color: " + colorVal, "background: #222; color: " + colorVal + "; font-size: 16px; padding: 5px;");
    }
};
LiteGraph.registerNodeType("custom/log", NodeLog);

async function loadWorkflow() {
    const response = await fetch('/api/workflows');
    const workflows = await response.json();
    if (workflows.length > 0) {
        // نأخذ آخر نسخة محفوظة
        let lastProject = workflows[workflows.length - 1];
        // إعادة رسم المشروع بالكامل مع الألوان المختارة
        graph.configure(lastProject.data); 
    }
}

// استدعاء التحميل عند بدء التشغيل
window.onload = loadWorkflow;
</script>

</body>
</html>