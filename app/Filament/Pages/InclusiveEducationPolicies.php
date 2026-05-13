<?php

namespace App\Filament\Pages;

use App\Filament\Resources\IepGoalResource;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;

class InclusiveEducationPolicies extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Inclusive Education Policies';

    protected string $view = 'filament.pages.inclusive-education-policies';

    public ?string $selectedDocument = null;

    public static function canAccess(): bool
    {
        return IepGoalResource::canAccess();
    }

    public function mount(): void
    {
        $this->selectedDocument = array_key_first($this->getDocuments());
    }

    public function selectDocument(string $documentKey): void
    {
        if (! array_key_exists($documentKey, $this->getDocuments())) {
            return;
        }

        $this->selectedDocument = $documentKey;

        $this->dispatch('scroll-to-policy-viewer');
    }

    public function viewer(Schema $schema): Schema
    {
        $document = $this->getSelectedDocument();

        return $schema->components([
            Section::make($document['title'])
                ->description('Viewing inline with the PDF viewer.')
                ->schema([
                    PdfViewerEntry::make('policy_document')
                        ->hiddenLabel()
                        ->fileUrl(asset($document['path']))
                        ->minHeight('80svh')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    /**
     * @return array<string, array{title: string, path: string}>
     */
    public function getDocuments(): array
    {
        return [
            'inclusive-education-policy' => [
                'title' => 'National Inclusive Education Policy',
                'path' => 'documents/policies/National Policy Document.pdf',
            ],
            // ...
        ];
    }

    /**
     * @return array{title: string, path: string}
     */
    public function getSelectedDocument(): array
    {
        return $this->getDocuments()[$this->selectedDocument] ?? array_values($this->getDocuments())[0];
    }
}
