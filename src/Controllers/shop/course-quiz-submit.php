<?php

use App\Auth\Auth;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Course;

Auth::requireCustomer();

$tenant = currentTenant();
$tenantId = currentTenantId();
$userId = currentUserId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/courses');
}

$quizId = (int)($_POST['quiz_id'] ?? 0);

$quiz = Quiz::find($quizId, $tenantId);
if (!$quiz || $quiz['status'] !== 'published') {
    flashMessage('error', 'Quiz not found.');
    redirect('/courses');
}

$course = Course::find($quiz['course_id'], $tenantId);
if (!$course) {
    flashMessage('error', 'Course not found.');
    redirect('/courses');
}

// Get all questions with choices
$questions = QuizQuestion::getByQuizId($quizId);
if (empty($questions)) {
    flashMessage('error', 'This quiz has no questions.');
    redirect('/course/' . $course['slug'] . '/learn');
}

// Grade the quiz
$totalQuestions = count($questions);
$correctAnswers = 0;
$answersLog = [];

foreach ($questions as $question) {
    $choices = QuizQuestion::getChoices($question['id']);
    $selectedChoiceId = (int)($_POST['question_' . $question['id']] ?? 0);

    $isCorrect = false;
    foreach ($choices as $choice) {
        if ($choice['id'] == $selectedChoiceId && $choice['is_correct']) {
            $isCorrect = true;
            break;
        }
    }

    if ($isCorrect) {
        $correctAnswers++;
    }

    $answersLog[] = [
        'question_id' => $question['id'],
        'selected_choice_id' => $selectedChoiceId,
        'correct' => $isCorrect,
    ];
}

$scorePercentage = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;
$passed = $scorePercentage >= (float)$quiz['pass_threshold'];

// Record attempt
$attemptId = QuizAttempt::create([
    'tenant_id' => $tenantId,
    'user_id' => $userId,
    'quiz_id' => $quizId,
    'score_percentage' => $scorePercentage,
    'passed' => $passed ? 1 : 0,
    'answers' => $answersLog,
    'ip_address' => getClientIp(),
]);

view('shop/quiz-result', [
    'tenant' => $tenant,
    'course' => $course,
    'quiz' => $quiz,
    'scorePercentage' => $scorePercentage,
    'correctAnswers' => $correctAnswers,
    'totalQuestions' => $totalQuestions,
    'passed' => $passed,
    'answersLog' => $answersLog,
]);
