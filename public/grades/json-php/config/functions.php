<?php

function isUrlAccessible($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 400);
}

function validateEducations($post, $total) :string
{
    $count = 1;

    for ($i = 1; $i <= $total; $i++) {

        if (!isset($post['edu_year' . $i]) || !isset($post['edu_school' . $i])) {
            continue;
        }
        if ($count > 9) {
            return "Maximum of 9 position entries exceeded! <br />";
        }
        if ( strlen($post['edu_year' . $i]) < 4 || strlen($post['edu_school' . $i]) == 0 ) {
            return "All Educations are required <br />";
        }
        if ( !is_numeric($post['edu_year' . $i]) ) {
            return "Education year must be numeric <br />";
        }
        $count++;
    }

    return '';
}

function insertEducations($pdo, $profileId, $post = [])
{
    $countEducationFields = intval($_POST['count_education_fields']);

    $rank = 1;
    for ($i = 1; $i <= $countEducationFields; $i++) {

        if (!isset($post['edu_year' . $i]) || !isset($post['edu_school' . $i])) {
            continue;
        }

        $query = "INSERT INTO Education
                    (profile_id, institution_id, education_rank, year)
                VALUES
                    ( :profileId, :institutionId, :rank, :year)";
        $stmt = $pdo->prepare($query);

        $stmt->execute([
            ':profileId' => $profileId,
            ':institutionId' => getInstitutionId($post['edu_school' . $i], $pdo),
            ':rank' => $rank,
            ':year' => intval($post['edu_year' . $i]),
        ]);

        $rank++;
    }
}

function deleteEducations($pdo, $profileId) :void
{
    $query = "DELETE FROM Education WHERE profile_id = :profileId";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':profileId' => $profileId]);
}

function getInstitutionId($school, $pdo)
{
    $stmt = $pdo->prepare('SELECT institution_id FROM Institution WHERE name = :school');
    $stmt->execute([':school' => $school]);
    $institution = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($institution !== false) {
        return $institution['institution_id'];
    }

    $query = "INSERT INTO Institution (name) VALUES (:school)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':school' => $school]);

    return $pdo->lastInsertId();
}

function validatePositions($post, $total) :string
{
    $count = 1;

    for ($i = 1; $i <= $total; $i++) {

        if (!isset($post['year' . $i]) || !isset($post['desc' . $i])) {
            continue;
        }
        if ($count > 9) {
            return "Maximum of 9 position entries exceeded! <br />";
        }
        if ( strlen($post['year' . $i]) < 4 || strlen($post['desc' . $i]) == 0 ) {
            return "All Positions are required <br />";
        }
        if ( !is_numeric($post['year' . $i]) ) {
            return "Position year must be numeric <br />";
        }
        $count++;
    }

    return '';
}

function insertPositions($pdo, $profileId, $post = [])
{
    $countPositionFields = intval($post['count_position_fields']);

    $rank = 1;
    for ($i = 1; $i <= $countPositionFields; $i++) {

        if (!isset($post['year' . $i]) || !isset($post['desc' . $i])) {
            continue;
        }

        $query = "INSERT INTO Position
                    (profile_id, position_rank, year, description)
                VALUES
                    ( :profileId, :rank, :year, :desc)";
        $stmt = $pdo->prepare($query);

        $stmt->execute([
            ':profileId' => $profileId,
            ':rank' => $rank,
            ':year' => intval($post['year' . $i]),
            ':desc' => htmlentities($post['desc' . $i]),
        ]);

        $rank++;
    }
}

function deletePositions($pdo, $profileId) :void
{
    $query = "DELETE FROM Position WHERE profile_id = :profileId";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':profileId' => $profileId]);
}
