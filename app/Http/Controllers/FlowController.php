<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Flow;

class FlowController extends Controller
{
   public function store(Request $request)
    {
        // التحقق من وصول البيانات
        if(!$request->has('data')) {
            return response()->json(['error' => 'No data provided'], 400);
        }

        $workflow = Flow::updateOrCreate(
            ['id' => 1], // سنقوم بحفظ مشروع واحد حالياً لتسهيل التجربة
            [
                'name' => $request->name ?? 'Default Project',
                'data' => $request->data // سيتم تحويله لـ JSON تلقائياً بفضل الـ Casts
            ]
        );

        return response()->json(['status' => 'success', 'data' => $workflow]);
    }
}
