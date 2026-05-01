<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flow;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    public function handleMessage(Request $request)
    {
        try {
            $userMessage = $request->input('message');
            $workflow = Flow::where('name', $userMessage)->first();

            $nodes = []; // تعريف المصفوفة لضمان إرسالها حتى لو لم توجد أتمتة
            $executionLog = [];

            if ($workflow) {
                // تحويل البيانات لمصفوفة
                $data = is_array($workflow->data) ? $workflow->data : json_decode($workflow->data, true);
                $nodes = $data['nodes'] ?? [];

                foreach ($nodes as $node) {
                    $properties = $node['properties'] ?? [];

                    // 1. فحص نود الـ Log (بناءً على manual_text)
                    if (isset($properties['manual_text'])) {
                        $msg = $properties['manual_text'];
                        $executionLog[] = "📝 Log: ($msg)";
                    }

                    // 2. فحص نود الـ Color (بناءً على selected_color)
                    if (isset($properties['selected_color'])) {
                        $color = $properties['selected_color'];
                        $executionLog[] = "🎨 Color: ($color)";
                    }
                }

                if (empty($executionLog)) {
                    $reply = "تم العثور على الأتمتة، ولكن لم يتم العثور على عقد (Nodes) متوافقة.";
                } else {
                    $reply = "تم تشغيل (" . $workflow->name . ") بنجاح: " . implode(" ", $executionLog);
                }
            } else {
                $reply = "عفواً، ما لقيت أتمتة باسم ($userMessage).";
            }

            // تخزين الرسالة في جدول المحادثات
            ChatMessage::create([
                'user_message' => $userMessage,
                'bot_reply'    => $reply,
            ]);

            // التعديل الجوهري هنا: نرسل الـ nodes مع الـ reply
            return response()->json([
                'status' => 'success', 
                'reply' => $reply, 
                'nodes' => $nodes // هذا السطر هو الذي سيقرأه الجافا سكربت ليطبع في الكونسول
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'reply' => 'خطأ برمجي: ' . $e->getMessage()]);
        }
    }
}