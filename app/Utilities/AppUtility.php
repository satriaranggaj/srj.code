<?php

namespace App\Utilities;

class AppUtility
{
    static function groupRelationshipString($parameter)
    {
        $output = [];

        if (!is_array($parameter)) {
            $arrayRelationships = explode(".", $parameter);

            if (count($arrayRelationships) == 1) return [];

            $relationship = $arrayRelationships[0];

            unset($arrayRelationships[0]);
            $fields = implode(".", $arrayRelationships);

            if (!isset($output[$relationship])) {
                $output[$relationship] = [];
            }

            $output[$relationship][] = $fields;

            return $output;
        }

        foreach ($parameter as $value) {
            $arrayRelationships = explode(".", $value);

            if (count($arrayRelationships) == 1) continue;

            $relationship = $arrayRelationships[0];

            unset($arrayRelationships[0]);
            $fields = implode(".", $arrayRelationships);

            if (!isset($output[$relationship])) {
                $output[$relationship] = [];
            }

            $output[$relationship][] = $fields;
        }

        return $output;
    }

    static function listView($query, $request, $searchable = [], $searchFormulas = [])
    {
        $request = [
            "search" => $request['search'] ?? null,
            "limit" => $request['limit'] ?? 10,
            "page" => $request['page'] ?? 1,
            "order" => $request['order'] ?? null,
            "dir" => $request['dir'] ?? null,
            "exact" => $request['exact'] ?? null,
        ];

        $search = $request['search'] ?? '';
        $take = $request['limit'];
        $skip = $take * ($request['page'] - 1);
        $exact = $request['exact'];


        $total = $query->count();

        if ($search) {
            $searchIndex = 0;
            $relationships = self::groupRelationshipString($searchable);

            $query->where(function ($q) use ($searchable, $search, $searchFormulas, $searchIndex, $relationships) {
                foreach ($searchable as $field) {
                    if (strpos($field, ".") !== false) continue;

                    if ($searchIndex == 0) {
                        $searchQuery = isset($searchFormulas[$field]) ? $searchFormulas[$field]($search) : $search;
                        $q->where($field, 'LIKE', "%$searchQuery%");
                    } else {
                        $searchQuery = isset($searchFormulas[$field]) ? $searchFormulas[$field]($search) : $search;
                        $q->orWhere($field, 'LIKE', "%$searchQuery%");
                    }

                    $searchIndex++;
                }

                foreach ($relationships as $relationship => $fields) {
                    if (strpos($relationship, ".") !== false) continue;

                    // Level 1 relationship
                    $q->orWhereHas($relationship, function ($q2) use ($fields, $search, $searchFormulas) {
                        foreach ($fields as $k => $field) {
                            if (strpos($field, ".") === FALSE) {
                                if ($k == 0) {
                                    $searchQuery = isset($searchFormulas[$field]) ? $searchFormulas[$field]($search) : $search;
                                    $q2->where($field, 'LIKE', "%$searchQuery%");
                                    continue;
                                }
                                $searchQuery = isset($searchFormulas[$field]) ? $searchFormulas[$field]($search) : $search;
                                $q2->orWhere($field, 'LIKE', "%$searchQuery%");
                                continue;
                            }

                            // Level 2 relationship
                            $relationships = self::groupRelationshipString([$field]);
                            foreach ($relationships as $relationship => $fields) {
                                if (strpos($relationship, ".") !== false) continue;

                                $q2->orWhereHas($relationship, function ($q3) use ($fields, $search, $searchFormulas) {
                                    foreach ($fields as $k => $field) {
                                        if (strpos($field, ".") === FALSE) {
                                            if ($k == 0) {
                                                $searchQuery = isset($searchFormulas[$field]) ? $searchFormulas[$field]($search) : $search;
                                                $q3->where($field, 'LIKE', "%$searchQuery%");
                                                continue;
                                            }

                                            $searchQuery = isset($searchFormulas[$field]) ? $searchFormulas[$field]($search) : $search;
                                            $q3->orWhere($field, 'LIKE', "%$searchQuery%");
                                            continue;
                                        }

                                        // Level 3 relationship
                                        $relationships = self::groupRelationshipString([$field]);
                                        foreach ($relationships as $relationship => $fields) {
                                            if (strpos($relationship, ".") !== false) continue;

                                            $q3->orWhereHas($relationship, function ($q4) use ($fields, $search, $searchFormulas) {
                                                foreach ($fields as $k => $field) {
                                                    if (strpos($field, ".") === FALSE) {
                                                        if ($k == 0) {
                                                            $searchQuery = isset($searchFormulas[$field]) ? $searchFormulas[$field]($search) : $search;
                                                            $q4->where($field, 'LIKE', "%$searchQuery%");
                                                            continue;
                                                        }
                                                        $searchQuery = isset($searchFormulas[$field]) ? $searchFormulas[$field]($search) : $search;
                                                        $q4->orWhere($field, 'LIKE', "%$searchQuery%");
                                                        continue;
                                                    }
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                }
            });

            // if ($havingSearchable) {
            //     foreach ($havingSearchable as $field) {
            //         $query->orHaving($field, "LIKE", "%$search%");
            //     }
            // }
        }

        // example: name:budi
        // example2: profile.name:budi
        if ($exact) {
            $exact = explode(":", $exact);
            $exactField = $exact[0];
            $exactValue = $exact[1];

            $query->where(function ($q) use ($exactField, $exactValue) {
                $level = explode(".", $exactField);

                if (count($level) == 1) {
                    $q->where($exactField, $exactValue);
                    return;
                }

                if (count($level) == 2) {
                    $q->whereHas($level[0], function ($q2) use ($level, $exactValue) {
                        $q2->where($level[1], $exactValue);
                    });
                    return;
                }

                if (count($level) == 3) {
                    $q->whereHas($level[0], function ($q2) use ($level, $exactValue) {
                        $q2->whereHas($level[1], function ($q3) use ($level, $exactValue) {
                            $q3->where($level[2], $exactValue);
                        });
                    });
                    return;
                }
            });
        }

        $filteredTotal = $query->count();
        $totalPage = $take && $filteredTotal > 0 ? ceil($filteredTotal / $take) : 1;

        $currentPage = 1;
        if ($skip !== null && $take !== null) {

            if ($skip > 0) {
                $currentPage += ceil($skip / $take);
            }

            $query->skip($skip);
            $query->take($take);
        }

        if (isset($request['order']) && $request['order'] && isset($request['dir']) && $request['dir']) {
            $query->orderBy($request['order'], $request['dir']);
        }

        return [
            "data" => $query->get(),
            "pagination" => [
                "current_page" => $currentPage,
                "page_total" => $totalPage,
                "filtered_data_total" => $filteredTotal,
                "data_total" => $total,
            ],
            "query" => $request
        ];
    }
}
