<?php

$rawPlannerAnchors = env('PLANNER_ANCHORS', true);
$normalizedPlannerAnchors = filter_var(
    $rawPlannerAnchors,
    FILTER_VALIDATE_BOOL,
    FILTER_NULL_ON_FAILURE
);

return [
    'anchors' => [
        'enabled' => $normalizedPlannerAnchors ?? (bool) $rawPlannerAnchors,
    ],
];
