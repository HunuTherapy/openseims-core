@php
    $rows = $getState() ?? [];
@endphp

<div class="space-y-3">
    @if (count($rows) === 0)
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
            No field-level changes were captured for this event.
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full divide-y divide-gray-200 text-sm" style="width: 100%; table-layout: fixed;">
                    <colgroup>
                        <col style="width: 24%;">
                        <col style="width: 24%;">
                        <col style="width: 52%;">
                    </colgroup>

                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left font-medium text-gray-700">Field</th>
                            <th class="px-6 py-4 text-left font-medium text-gray-700">Previous value</th>
                            <th class="px-6 py-4 text-left font-medium text-gray-700">New value</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($rows as $row)
                            <tr>
                                <td class="align-top px-6 py-4 font-medium text-gray-900">{{ $row['field'] }}</td>
                                <td class="align-top px-6 py-4 whitespace-pre-wrap break-words text-gray-600">{{ $row['old'] }}</td>
                                <td class="align-top px-6 py-4 whitespace-pre-wrap break-words text-gray-900">{{ $row['new'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
