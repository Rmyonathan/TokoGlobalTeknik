<!DOCTYPE html>
<html>
<head>
    <title>Test</title>
</head>
<body>
    <h1>Test View</h1>
    <p>Cutting History exists: {{ isset($cuttingHistory) ? 'Yes' : 'No' }}</p>
    <p>Cutting History count: {{ isset($cuttingHistory) ? $cuttingHistory->count() : 'N/A' }}</p>
    <p>Inventory exists: {{ isset($inventory) ? 'Yes' : 'No' }}</p>
</body>
</html>