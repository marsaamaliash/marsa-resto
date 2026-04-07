<?php

use App\Livewire\Holdings\Campus\LMS\Material\LmsMaterialUpload;
use App\Livewire\Holdings\Campus\LMS\Quiz\LmsQuizCreate;
use App\Livewire\Holdings\Campus\LMS\Quiz\LmsQuizPlay;
use App\Livewire\Holdings\Campus\LMS\Quiz\LmsResultView;
use App\Livewire\Holdings\Campus\LMS\Room\LmsRoomCreate;
use App\Livewire\Holdings\Campus\LMS\Room\LmsRoomJoin;
use App\Livewire\Holdings\Campus\LMS\Room\LmsRoomManage;
use App\Livewire\Holdings\Campus\LMS\Video\LmsVideoFrame;
use App\Livewire\Holdings\Campus\LMS\Video\LmsVideoSessionView;
use App\Livewire\Holdings\Campus\Siakad\Student\StudentCreate;
use App\Livewire\Holdings\Campus\Siakad\Student\StudentTable;
use Illuminate\Support\Facades\Route;

Route::prefix('holdings/campus/lms')->name('holdings.campus.lms.')->middleware(['auth', 'role:dosen'])->group(function () {
    // Room
    Route::get('/room/create', LmsRoomCreate::class)->name('room.create');
    Route::get('/room/manage/{room}', LmsRoomManage::class)->name('room.manage');
    Route::get('/room/join/{room}', LmsRoomJoin::class)->name('room.join');

    // Material
    Route::get('/material/upload/{room}', LmsMaterialUpload::class)->name('material.upload');

    // Quiz
    Route::get('/quiz/create/{room}', LmsQuizCreate::class)->name('quiz.create');
    Route::get('/quiz/play/{quiz}', LmsQuizPlay::class)->name('quiz.play');
    Route::get('/quiz/result/{quiz}', LmsResultView::class)->name('quiz.result');
    Route::view('/quiz', 'holdings.campus.lms.quiz.lms-quiz')->name('quiz');

    // Video
    Route::get('/video/{room}', LmsVideoFrame::class)->name('video');
    Route::get('/video/session/{room}', LmsVideoSessionView::class)->name('video.session');
});

Route::prefix('holdings/campus/siakad/student')->name('holdings.campus.siakad.student.')->group(function () {
    Route::get('/', StudentTable::class)->name('students-table');
    Route::get('/create', StudentCreate::class)->name('students-create');
    // Route::get('/{nim}', StudentShow::class)->name('student-show');
    // Route::get('/{nim}/edit', StudentEdit::class)->name('student-edit');
});
