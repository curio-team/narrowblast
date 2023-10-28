<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
    html, body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    table {
        width: 100%;
        text-align: center;
        table-layout: fixed;
    }
    table tr td {
        border-spacing: 1em;
    }
    .ticket {
        padding: 1rem;
    }
    .code {
        border: 1px solid #e2e8f0;
        border-radius: 0.25rem;
        padding: 1rem;
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.1rem;
    }
    .credits {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 0.5rem;
    }
    .timestamp {
        font-size: 0.75rem;
        line-height: 1.2;
        margin-top: 0.5rem;
        color: #7d8997;
        display: block;
    }
</style>
<table>
    @foreach($creditCodes as $i => $creditCode)
        @if($i % 3 == 0)
            <tr>
        @endif
        <td class="ticket">
            <div class="credits">{{ $creditCode->credits }} credits</div>
            <div class="code">{{ $creditCode->code }}</div>
            <span class="timestamp">{{ $creditCode->printed_at }}</span>
        </td>
        @if($i % 3 == 2)
            </tr>
        @endif
    @endforeach
</table>
