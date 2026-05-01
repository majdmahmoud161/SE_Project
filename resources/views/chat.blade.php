<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Vibe Code Bot</title>
    <style>
        body { font-family: sans-serif; background: #1a1a1a; color: white; display: flex; justify-content: center; padding: 20px; }
        .chat-container { width: 400px; background: #2d2d2d; border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; height: 500px; }
        #chat-box { flex: 1; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; }
        .message { padding: 8px 12px; border-radius: 15px; max-width: 80%; }
        .user { background: #007bff; align-self: flex-end; }
        .bot { background: #444; align-self: flex-start; }
        .input-area { display: flex; padding: 10px; background: #333; }
        input { flex: 1; padding: 10px; border: none; border-radius: 5px; outline: none; }
        button { padding: 10px; background: #28a745; color: white; border: none; cursor: pointer; margin-right: 5px; border-radius: 5px; }
    </style>
</head>
<body>

<div class="chat-container">
    <div id="chat-box">
        <div class="message bot">أهلاً بك! اكتب اسم الأتمتة لتشغيلها.</div>
    </div>
    <div class="input-area">
        <input type="text" id="user-input" placeholder="اكتب رسالتك هنا...">
        <button onclick="sendMessage()">إرسال</button>
    </div>
</div>
<script>
    async function sendMessage() {
        let input = document.getElementById('user-input');
        let chatBox = document.getElementById('chat-box');
        let chatContainer = document.querySelector('.chat-container'); // تحديد الحاوية لتغيير لونها
        let message = input.value;
        if(!message) return;

        // عرض رسالة المستخدم
        chatBox.innerHTML += `<div class="message user">${message}</div>`;
        input.value = '';

        try {
            let response = await fetch('/chat/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: message })
            });

            let data = await response.json();
            
            // 1. عرض رد البوت في الشات
            chatBox.innerHTML += `<div class="message bot">${data.reply}</div>`;
            chatBox.scrollTop = chatBox.scrollHeight;

            // 2. دليل التنفيذ في الكونسول (Console)
            if (data.nodes && data.nodes.length > 0) {
                console.log("%c🚀 بدء تنفيذ الأتمتة: " + message, "color: #28a745; font-weight: bold; font-size: 14px;");
                
                data.nodes.forEach(node => {
                    if (node.type === 'custom/log') {
                        // طباعة النص الموجود داخل نود الـ Log
                        console.log("%c📝 تنفيذ نود [Log]:", "color: #ffc107;", node.properties.manual_text);
                    }
                    if (node.type === 'custom/color') {
                        // طباعة اللون وتغيير ستايل الشات كدليل بصري
                        let selectedColor = node.properties.selected_color;
                        console.log("%c🎨 تنفيذ نود [Color]:", "color: #17a2b8;", selectedColor);
                        
                        // تغيير لون حدود الشات ليكون دليل ملموس بالواجهة
                        chatContainer.style.border = `2px solid ${selectedColor}`;
                        chatContainer.style.boxShadow = `0 0 15px ${selectedColor}`;
                    }
                });
                
                console.log("%c✅ انتهى تنفيذ جميع العقد بنجاح.", "color: #28a745; font-weight: bold;");
            }

        } catch (error) {
            console.error("خطأ في الاتصال بالسيرفر:", error);
        }
    }
</script>

</body>
</html>