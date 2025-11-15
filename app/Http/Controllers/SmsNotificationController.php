<?php

namespace App\Http\Controllers;

use App\Facades\Sms;
use App\Models\User;
use App\Models\Grade;
use App\Models\Assignment;
use Illuminate\Http\Request;

/**
 * Контроллер для отправки SMS уведомлений
 */
class SmsNotificationController extends Controller
{
    /**
     * Отправить уведомление о новой оценке
     */
    public function notifyNewGrade(Grade $grade)
    {
        $student = $grade->student;
        
        if (!$student->phone) {
            return [
                'success' => false,
                'message' => 'У студента не указан номер телефона'
            ];
        }

        $subject = optional($grade->schedule)->subject;
        $subjectName = $subject ? $subject->name : 'предмету';

        return Sms::sendGradeNotification(
            $student->phone,
            $grade->grade,
            $subjectName
        );
    }

    /**
     * Отправить уведомление о новом задании
     */
    public function notifyNewAssignment(Assignment $assignment, $studentIds = [])
    {
        $results = [];
        
        // Если указаны конкретные студенты
        if (!empty($studentIds)) {
            $students = User::whereIn('id', $studentIds)
                ->whereNotNull('phone')
                ->get();
        } 
        // Иначе отправляем всей группе
        elseif ($assignment->group_id) {
            $students = User::where('group_id', $assignment->group_id)
                ->whereNotNull('phone')
                ->get();
        } else {
            return [
                'success' => false,
                'message' => 'Не указаны получатели'
            ];
        }

        foreach ($students as $student) {
            $result = Sms::sendAssignmentNotification(
                $student->phone,
                $assignment->title,
                $assignment->due_date ? $assignment->due_date->format('d.m.Y') : 'не указан'
            );
            
            $results[] = [
                'student' => $student->name,
                'phone' => $student->phone,
                'result' => $result
            ];
        }

        return [
            'success' => true,
            'sent' => count($results),
            'details' => $results
        ];
    }

    /**
     * Отправить код верификации пользователю
     */
    public function sendVerificationCode(User $user)
    {
        if (!$user->phone) {
            return response()->json([
                'success' => false,
                'message' => 'У пользователя не указан номер телефона'
            ], 400);
        }

        $code = rand(100000, 999999);
        
        // Сохраняем код в кэше на 10 минут
        cache()->put("verification_code_{$user->id}", $code, now()->addMinutes(10));

        $result = Sms::sendVerificationCode($user->phone, $code);

        return response()->json($result);
    }

    /**
     * Отправить уведомление о входе в систему
     */
    public function sendLoginNotification(User $user)
    {
        if (!$user->phone) {
            return [
                'success' => false,
                'message' => 'У пользователя не указан номер телефона'
            ];
        }

        return Sms::sendLoginNotification($user->phone);
    }

    /**
     * Отправить массовое уведомление группе
     */
    public function sendBulkNotification(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'message' => 'required|string|max:160',
        ]);

        $students = User::where('group_id', $request->group_id)
            ->whereNotNull('phone')
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'В группе нет студентов с указанными номерами телефонов'
            ], 400);
        }

        $phones = $students->pluck('phone')->toArray();
        $result = Sms::send($phones, $request->message);

        return response()->json([
            'success' => true,
            'recipients' => count($phones),
            'message' => 'Уведомления отправлены',
            'details' => $result
        ]);
    }

    /**
     * Отправить уведомление об изменении расписания
     */
    public function notifyScheduleChange($groupId, $message)
    {
        $students = User::where('group_id', $groupId)
            ->whereNotNull('phone')
            ->get();

        $results = [];
        foreach ($students as $student) {
            $result = Sms::send($student->phone, $message . ' - IFTUT.TJ');
            $results[] = [
                'student' => $student->name,
                'result' => $result
            ];
        }

        return [
            'success' => true,
            'sent' => count($results),
            'details' => $results
        ];
    }
}

