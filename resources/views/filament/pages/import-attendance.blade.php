<x-filament-panels::page>

    <p>
        Hier kun je de aanwezigheid van de studenten importeren om ze automatisch credits te laten verdienen. <em>We importeren aan het eind van iedere lesweek</em>
    </p>

    <p>
        Om een geschikte export te maken kies je in PARS:

        <div class="flex flex-col md:flex-row gap-4">
            <ol class="list-decimal ml-4 list-inside shrink-0">
                <li>Rapportages</li>
                <li>Rapportages per groep</li>
                <li>Rapportage per groep, alleen aantallen</li>
                <li>Selecteer deze lesweek (van maandag t/m vrijdag)</li>
                <li>Selecteer alle klassen</li>
                <li>View Report</li>
                <li>Klik op de diskette en sla op als .csv</li>
            </ol>

            <img src="{{ Vite::asset('resources/images/pars-export-option.png') }}"
                class="shadow rounded w-full object-cover"
                alt="pars export option">
        </div>
    </p>

    {{ $this->importAction() }}

</x-filament-panels::page>
