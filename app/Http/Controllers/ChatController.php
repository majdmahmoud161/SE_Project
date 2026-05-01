<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flow;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Http; // ضروري جداً

class ChatController extends Controller
{
 public function handleMessage(Request $request)
{
    try {
        $userMessage = trim($request->input('message'));
        $availableWorkflows = \App\Models\Flow::all()->pluck('name')->toArray();
        $workflowsList = implode(', ', $availableWorkflows);
        
        // المفتاح الجديد تبعك
        $apiKey = 'AIzaSyA__-6-BuicZG9_8rrQZsAvFy1TvqrmoDM'; 

        // الرابط الصحيح والموديل المتاح بقائمتك حرفياً
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key={$apiKey}";

        $response = Http::post($url, [
            'contents' => [['parts' => [['text' => "Match user intent to one name from this list: [{$workflowsList}]. User said: '{$userMessage}'. Return ONLY the name or 'none'."]]]]
        ]);

        if ($response->failed()) {
            return response()->json(['status' => 'error', 'reply' => 'خطأ من جوجل: ' . $response->body()]);
        }

        $data = $response->json();
        $chosenWorkflowName = trim(str_replace(["\n", "\r", ".", "*"], '', $data['candidates'][0]['content']['parts'][0]['text'] ?? 'none'));

        $workflow = \App\Models\Flow::where('name', 'LIKE', '%' . $chosenWorkflowName . '%')->first();

        if ($workflow && strtolower($chosenWorkflowName) !== 'none') {
            $reply = "تمت المطابقة بنجاح: " . $workflow->name;
            $flowData = is_array($workflow->data) ? $workflow->data : json_decode($workflow->data, true);
            $nodes = $flowData['nodes'] ?? [];
        } else {
            $reply = "الـ AI رد بـ: (" . $chosenWorkflowName . ") والقائمة كانت: [" . $workflowsList . "]";
            $nodes = [];
        }

        return response()->json(['status' => 'success', 'reply' => $reply, 'nodes' => $nodes]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'reply' => 'عطل برمجي: ' . $e->getMessage()]);
    }
}
}