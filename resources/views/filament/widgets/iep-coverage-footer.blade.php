<div
    x-data="{ copied: false }"
    class="border rounded-xl border-slate-200 p-4 shadow-xs"
    role="region"
    aria-label="AI Summary"
>
    <!-- Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="inline-flex items-center gap-2">
            <x-heroicon-o-sparkles class="h-5 w-5 text-amber-500" />
            <h3 class="text-sm font-semibold text-slate-800">AI Summary</h3>
        </div>

        <!-- Copy button -->
        <button
            type="button"
            x-on:click="navigator.clipboard.writeText($refs.aiSum.innerText); copied = true; setTimeout(() => copied = false, 1200)"
            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50 focus:outline-hidden focus:ring-2 focus:ring-emerald-400"
        >
            <x-heroicon-o-document-duplicate class="h-4 w-4" />
            <span x-show="!copied">Copy</span>
            <span x-show="copied" class="inline-flex items-center gap-1 text-emerald-600">
                <x-heroicon-s-check class="h-4 w-4" /> Copied
            </span>
        </button>
    </div>

    <!-- Body -->
    <div class="mt-3 flex gap-3">
        <p x-ref="aiSum" class="whitespace-pre-line text-sm leading-6 text-slate-700">
            IEP coverage is highly uneven across regions, ranging from 25% in Bono, Central, Upper West, and Volta to 100% in North East, with Savannah (80%), Northern (50%), Western North (40%), Greater Accra (33.3%), and Ashanti (30%) in between. Several regions (e.g., Eastern, Upper East, Western) show near-zero or missing values—likely incomplete reporting. The pattern suggests stronger IEP uptake in parts of the northern belt and weaker coverage across several southern regions. <br> <br> Suggested next steps: validate denominators and reporting completeness, confirm a consistent IEP definition in data entry, then target support to persistently low-coverage regions.
        </p>
    </div>

    <!-- Footer meta -->
    <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1">
            <x-heroicon-o-clock class="h-4 w-4" /> Generated two minutes ago
        </span>
        <span class="inline-flex items-center gap-1">
            <x-heroicon-o-shield-check class="h-4 w-4" /> AI-generated reports may contain inaccurate data.
        </span>
    </div>
</div>
