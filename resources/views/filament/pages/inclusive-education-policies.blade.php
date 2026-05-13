<x-filament-panels::page
    x-data
    x-on:scroll-to-policy-viewer.window="$nextTick(() => document.getElementById('policy-viewer')?.scrollIntoView({ behavior: 'smooth', block: 'start' }))"
>
    @php
        $documents = $this->getDocuments();
    @endphp

    <x-filament::section>
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-gray-950">Document</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-gray-950">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($documents as $key => $document)
                        <tr wire:key="policy-document-{{ $key }}" @class([
                            'bg-warning-50' => $this->selectedDocument === $key,
                            'bg-white' => $this->selectedDocument !== $key,
                        ])>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $document['title'] }}</td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    type="button"
                                    wire:click="selectDocument('{{ $key }}')"
                                    class="inline-flex items-center rounded-lg bg-warning-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-warning-600"
                                >
                                    Open PDF
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>

    <div id="policy-viewer">
        {{ $this->viewer }}
    </div>
</x-filament-panels::page>
