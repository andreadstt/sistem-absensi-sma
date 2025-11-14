<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Imports\StudentsImport;
use App\Exports\StudentsTemplateExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Download Template
            Actions\Action::make('download_template')
                ->label('Download Template Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    return Excel::download(new StudentsTemplateExport(), 'template_siswa.xlsx');
                }),

            // Import Excel
            Actions\Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->helperText('Upload file Excel dengan format: Nama, NIS, Kelas, Email (Opsional)')
                ])
                ->action(function (array $data) {
                    try {
                        $file = Storage::disk('public')->path($data['file']);
                        $import = new StudentsImport();
                        
                        Excel::import($import, $file);
                        
                        Storage::disk('public')->delete($data['file']);
                        
                        $successCount = $import->getSuccessCount();
                        $skippedCount = $import->getSkippedCount();
                        $errors = $import->getErrors();
                        
                        if ($successCount > 0) {
                            $message = "Berhasil import {$successCount} siswa.";
                            if ($skippedCount > 0) {
                                $message .= " {$skippedCount} baris dilewati.";
                            }
                            
                            Notification::make()
                                ->title('Import Berhasil!')
                                ->body($message)
                                ->success()
                                ->send();
                        }
                        
                        if (!empty($errors)) {
                            Notification::make()
                                ->title('Beberapa Data Gagal Diimport')
                                ->body(implode("\n", array_slice($errors, 0, 5)))
                                ->warning()
                                ->send();
                        }
                        
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Gagal!')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Bulk Create
            Actions\Action::make('bulk_create')
                ->label('Tambah Multiple Siswa')
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->url(fn () => route('filament.admin.resources.students.bulk-create')),

            // Regular Create
            Actions\CreateAction::make()
                ->label('Tambah Siswa'),
        ];
    }
}
