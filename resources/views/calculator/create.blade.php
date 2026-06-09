{{-- Include the full admin view content but with agent routes --}}
@php
    // Override the route names for agent access
    $calculateRoute = 'calculator.calculate';
    $pdfRoute = 'calculator.pdf';
    $printRoute = 'calculator.print';
    $schoolDetailsRoute = 'schools.get-details';
    $schoolAirportsRoute = 'schools.get-airports';
@endphp

@include('admin.quotations.create')
