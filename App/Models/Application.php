<?php

/**
 * Application model
 *
 * PHP version 7.4
 */

namespace App\Models;

use \App\Models\Helpers\MySQL;
use \App\Models\Helpers\Validation\Validator as Validator;
use \App\Models\Helpers\Sanitization\Sanitize as Sanitize;
use \App\Models\Helpers\Password as Password;

class Application
{
    private $dbConn;

    public function __construct()
    {
        $this->dbConn = new MySQL();
    }

    private function checkAppExists(string $regNumber)
    {
        $request = $this->dbConn
            ->selectQuery('all', 'applications')
            ->simpleWhereQuery(array('registration_number' => $regNumber))
            ->getRows();

        return $request;
    }

    private function saveApplication(iterable $appData)
    {
        $insert = $this->dbConn
            ->insertQuery($appData, 'applications')
            ->insertRow();

        if (!empty($insert)) {
            $applicationId = $this->dbConn->getInsertedId();
            return $applicationId;
        }
    }

    private function updateApplication(iterable $appData, int $applicationId)
    {
        $update = $this->dbConn
            ->updateQuery($appData, 'applications')
            ->simpleWhereQuery(array('id' => $applicationId))
            ->insertRow();

        return $update;
    }

    private function saveExperience(iterable $experienceProof, int $applicationId)
    {
        $success = true;

        foreach ($experienceProof as $experience) {
            $experience['application_id'] = $applicationId;
            $insert = $this->dbConn
                ->insertQuery($experience, 'experience_proof')
                ->insertRow();

            if (!$insert) {
                $success = false;
            }
        }

        return $success;
    }

    private function deleteExperience(int $applicationId)
    {
        $delete =  $this->dbConn
            ->deleteQuery('experience_proof')
            ->simpleWhereQuery(array('application_id' => $applicationId))
            ->deleteRow();
    }

    private function generateRandomString(int $length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getApplicationById(int $id, bool $getDouble = false)
    {
        $request = $this->dbConn
            ->selectQuery('all', 'applications')
            ->simpleWhereQuery(array('id' => $id))
            ->limitQuery(1)
            ->getRows();

        if (empty($request)) {
            return false;
        }

        if (empty($getDouble)) {
            $doubled = $this->checkAppExists($request[0]['registration_number']);

            if (!empty($doubled) && count($doubled) >= 2) {
                return $doubled;
            }
        }

        return array_shift($request);
    }


    public function getApplicationList(string $type)
    {
        $where = [];

        switch ($type) {
            case 'all':
                $request = $this->dbConn
                    ->selectQuery('all', 'applications')
                    ->getRows();
                break;
            case 'matched':
                $request = $this->dbConn
                    ->selectQuery('all', 'applications')
                    ->groupByQuery('registration_number')
                    ->havingCountQuery()
                    ->getRows();
                break;
            case 'unmatched':
                $request = $this->dbConn
                    ->rawQuery('SELECT * FROM applications WHERE registration_number NOT IN (SELECT registration_number FROM applications GROUP BY registration_number HAVING COUNT(*) > 1)')
                    ->getRows();
                break;
            case 'identical':
                $request = $this->dbConn
                    ->selectQuery('all', 'applications')
                    ->groupByQuery('applications.hash')
                    ->havingCountQuery()
                    ->getRows();
                break;
            case 'different':
                $request = $this->dbConn
                    ->rawQuery('SELECT * FROM applications WHERE applications.hash NOT IN (SELECT applications.hash FROM applications GROUP BY applications.hash HAVING COUNT(*) > 1) AND registration_number IN (SELECT registration_number FROM applications GROUP BY registration_number HAVING COUNT(*) > 1)')
                    ->getRows();
                break;
            default:
                $request = $this->dbConn
                    ->selectQuery('all', 'applications')
                    ->getRows();
                break;
        }

        return !empty($request) ? $request : false;
    }

    public function getExperienceById(int $id)
    {
        $request = $this->dbConn
            ->selectQuery('all', 'experience_proof')
            ->simpleWhereQuery(array('application_id' => $id))
            ->getRows();

        return !empty($request) ? $request : false;
    }

    public function prepareApplication(iterable $data, iterable $diplomaData, iterable $positionData, iterable $mainQualData, iterable $addQualData, iterable $postgradData, int $id = null, iterable $appDbData = [])
    {
        global $session;

        $diplomaArr = $positionArr = $mainQualArr = $addQualArr = $postgradArr = [];

        foreach ($diplomaData as $value) {
            $diplomaArr[] = $value['diploma_id'];
        }

        foreach ($positionData as $value) {
            $positionArr[] = $value['position_id'];
        }

        foreach ($mainQualData as $value) {
            $mainQualArr[] = $value['main_qualification_id'];
        }

        foreach ($addQualData as $value) {
            $addQualArr[] = $value['additional_qualification_id'];
        }

        foreach ($postgradData as $value) {
            $postgradArr[] = $value['postgraduate_id'];
        }

        $noEmptyFields = ['lastname', 'firstname', 'father_name', 'mother_name', 'date_of_birth', 'gender', 'identity_number', 'registration_number', 'residence', 'address', 'address_number', 'zip', 'phone', 'mobile', 'email'];

        $inArrayFields = ['msc1_id', 'msc2_id', 'phd1_id', 'phd2_id'];

        $yesNoArrFields = ['accountant_identity', 'project_manager_cert', 'automation_it_knowledge', 'fin_cofinance_mng', 'state_aid_actions_mng', 'data_center', 'project_manag_xp'];

        $integerFields = ['efka_employee', 'efka_self_employed'];

        $plainFields = ['children', 'end_datetime', 'months', 'days', 'employer', 'employer_category', 'employment_subject'];

        $experienceProofFields = ['start_datetime', 'end_datetime', 'months', 'days', 'employer', 'employer_category', 'employment_subject', 'position_ids'];

        $noWhiteSpacesFields = ['registration_number'];

        $novalidateTextareas = ['applicant_notes', 'diplomas_notes', 'position_notes', 'criteria_notes', 'experience_notes', 'experience_proof_notes'];

        $checkForEmptyVals = $rules = $errors = $applicationData = $experienceProof = [];

        $success = true;

        $applicationData['not_valid'] = !empty($data['not_valid']) ? 1 : 0;


        foreach ($data as $field => $value) {
            if (in_array($field, $noEmptyFields)) {
                if (empty($applicationData['not_valid'])) {
                    // if it is a valid applicatioon form, procceed to validation
                    $rules[$field] = ['required' => $value];

                    if (in_array($field, $noWhiteSpacesFields)) {
                        $applicationData[$field] = preg_replace('/\s+/', '', $value);
                    } else {
                        $applicationData[$field] = $value;
                    }
                } else {
                    if (in_array($field, $noWhiteSpacesFields)) {
                        $applicationData[$field] = preg_replace('/\s+/', '', $value);
                    } else {
                        $applicationData[$field] = $value;
                    }

                    if ($field === 'registration_number' && empty($value)) {
                        $applicationData[$field] = 'ΚΕΝΟ - ' . $this->generateRandomString();
                    }
                }
            }

            if ($field === 'protocol_number') {
                $applicationData[$field] = $value;
            }

            if (in_array($field, $inArrayFields)) {
                if (!empty($value)) {
                    $rules[$field] = ['inarray' => json_encode($postgradArr)];
                    $applicationData[$field] = $value;
                } else {
                    $applicationData[$field] = null;
                }
            }

            if ($field === 'foreign_languages') {
                $languagesArr = [];
                foreach ($value as $language => $level) {
                    if (!in_array($language, ['english', 'french', 'german'])) {
                        $errors[] = 'Η γλώσσα που επιλέξατε δεν είναι σωστή';
                    }

                    if (!empty($level)) {
                        if (!in_array($level, ['excelent', 'very_good', 'good'])) {
                            $errors[] = 'Το επίπεδο γνώσης ξένων γλωσσών που επιλέξατε δεν είναι σωστό';
                        }
                        $languagesArr[$language] = $level;
                    } else {
                        $languagesArr[$language] = '';
                    }
                }
                if (!empty($languagesArr)) {
                    $applicationData[$field] = json_encode($languagesArr, JSON_UNESCAPED_UNICODE);
                }
            }

            if (in_array($field, $yesNoArrFields)) {
                $rules[$field] = ['inarray' => json_encode(['0', '1'])];

                $applicationData[$field] = $value;
            }

            if (in_array($field, $integerFields)) {
                if (!empty($value)) {
                    $rules[$field] = ['integer' => (int) $value];

                    $applicationData[$field] = $value;
                } else {
                    $applicationData[$field] = 0;
                }
            }

            if (in_array($field, $novalidateTextareas)) {
                //				$sanitizedVal = Sanitize::sanitize('string-value', $value);
                //				$applicationData[$field] = !empty($sanitizedVal) ? $sanitizedVal : null;
                $applicationData[$field] = !empty($value) ? $value : null;
            }

            if ($field === 'children') {
                $applicationData[$field] = $value;
            }

            if ($field === 'accountant_identity_date') {
                if (!empty($data['accountant_identity'])) {
                    $applicationData[$field] = $value;
                }
            }

            if ($field === 'xp_proof') {
                foreach ($value as $key=>$xpArr) {
                    if (is_array($xpArr)) {
                        $xpFieldsOk = true;
                        $xpValuesOk = false;
                        $xpProof = [];
                        foreach ($xpArr as $k=>$val) {
                            if (!in_array($k, $experienceProofFields)) {
                                $xpFieldsOk = false;
                            }
                            if (!empty($val)) {
                                $xpValuesOk = true;
                            }

                            if ($xpFieldsOk) {
                                if ($k === 'position_ids') {
                                    if (!empty($val)) {
                                        $posIds = [];
                                        foreach ($val as $v) {
                                            if (in_array($v, [1,2,3,4,5,6,7,8,9,10])) {
                                                $posIds[] = $v;
                                            }
                                        }
                                        $xpProof[$k] = json_encode($posIds);
                                    } else {
                                        $xpProof[$k] = null;
                                    }
                                } elseif ($k === 'months') {
                                    if (empty($val)) {
                                        $xpValuesOk = false;
                                    } else {
                                        $xpProof[$k] = $val;
                                    }
                                } else {
                                    $xpProof[$k] = !empty($val) ? $val : null;
                                }
                            }
                        }
                        $experienceProof[] = $xpProof;
                    }
                }
            }
        }

        $rules['children'] = ['inarray' => json_encode(['0', '1', '2'])];

        $rules['gender'] = ['inarray' => json_encode(['ΑΝΤΡΑΣ', 'ΓΥΝΑΙΚΑ'])];

        $errorArr = Validator::validate($data, $rules);

        foreach ($errorArr as $err) {
            if (!empty($err)) {
                $errors[] = $err;
            }
        }

        $diplomaSet = false;

        foreach ($data['diploma'] as $diploma) {
            $diplomasArr = [];

            if (!empty($diploma['diploma_id']) && !empty($diploma['diploma_degree']) && !empty($diploma['diploma_year'])) {
                $diplomaSet = true;
                $rules['diploma_id'] = ['inarray' => json_encode($diplomaArr)];
                $errorArr = Validator::validate($diploma, $rules);
                foreach ($errorArr as $err) {
                    if (!empty($err)) {
                        $errors[] = $err;
                    }
                }
            }

            foreach ($diploma as $key => $value) {
                if (!empty($value)) {
                    $diplomasArr[$key] = $value;
                }
            }

            if (!empty($diplomasArr)) {
                $applicationData['diplomas'][] = $diplomasArr;
            }
        }

        if (!empty($applicationData['diplomas'])) {
            $applicationData['diplomas'] = json_encode($applicationData['diplomas'], JSON_UNESCAPED_UNICODE);
        }

        if (!$diplomaSet && empty($applicationData['not_valid'])) {
            $errors[] = 'Θα πρέπει να καταχωρήσετε τουλάχιστον ένα(1) τίτλο σπουδών με το βαθμό και τη χρονιά απόκτησής του';
        }

        $positionSet = false;

        foreach ($data['position'] as $position) {
            $positionsArr = [];

//            if (!empty($position['position_id']) && !empty($position['position_qualification']) && !empty($position['position_add_qualification'])) {
            if (!empty($position['position_id']) && !empty($position['position_qualification'])) {
                $positionSet = true;
                $rules['position_id'] = ['inarray' => json_encode($positionArr)];

                $positionsErr = [];
                $positionsToCheckArr = [];

                foreach ($position['position_qualification'] as $value) {
                    $positionsToCheckArr[] = ['position_qualification' => $value];
                }

                $positionRules['position_qualification'] = ['inarray' => json_encode($mainQualArr)];

                if (!empty($position['position_add_qualification'])) {
                    foreach ($position['position_add_qualification'] as $value) {
                        $positionsToCheckArr[] = ['position_add_qualification' => $value];
                    }

                    $positionRules['position_add_qualification'] = ['inarray' => json_encode($addQualArr)];
                }

                $errorArr = Validator::validate($position, $rules);

                $positionsErr = Validator::validate($positionsToCheckArr, $positionRules);

                foreach ($errorArr as $err) {
                    if (!empty($err)) {
                        $errors[] = $err;
                    }
                }

                foreach ($positionsErr as $err) {
                    if (!empty($err)) {
                        $errors[] = $err;
                    }
                }
            }

            foreach ($position as $key => $value) {
                if (!empty($value)) {
                    if ($key === 'position_experience') {
                        if (!empty($position['position_add_qualification']) && in_array('1', $position['position_add_qualification'])) {
                            $positionsArr[$key] = $value;
                        }
                    } elseif ($key === 'position_qualification' || $key === 'position_add_qualification') {
                        if (!empty($value)) {
                            foreach ($value as $val) {
                                $positionsArr[$key][] = $val;
                            }
                        }
                    } else {
                        $positionsArr[$key] = $value;
                    }
                }
            }

            if (!empty($positionsArr)) {
                $applicationData['positions'][] = $positionsArr;
            }
        }

        if (!empty($applicationData['positions'])) {
            $applicationData['positions'] = json_encode($applicationData['positions'], JSON_UNESCAPED_UNICODE);
        }

        if (!$positionSet && empty($applicationData['not_valid'])) {
            $errors[] = 'Θα πρέπει να καταχωρήσετε τουλάχιστον μία(1) επιδιωκόμενη θέση';
        }

        $hashedXpBlocks = [];

        foreach ($experienceProof as $xp) {
            unset($xp['employer']);
            unset($xp['employer_category']);
            unset($xp['employment_subject']);
            unset($xp['start_datetime']);
            unset($xp['end_datetime']);
            unset($xp['days']);

            $hashedXpBlocks[] = hash('md5', json_encode($xp));
        }

        sort($hashedXpBlocks);

        $mergedAppExp = array_merge($applicationData, $hashedXpBlocks);

        unset($mergedAppExp['applicant_notes']);
        unset($mergedAppExp['diplomas_notes']);
        unset($mergedAppExp['position_notes']);
        unset($mergedAppExp['criteria_notes']);
        unset($mergedAppExp['experience_notes']);
        unset($mergedAppExp['experience_proof_notes']);

        $applicationData['hash'] = hash('sha256', json_encode($mergedAppExp));

        $applicationData['user'] = !empty($session->fullname) ? $session->fullname : "";


        //		echo "<pre>";
        //		print_r($experienceProof);
        //		print_r($applicationData);
        //		die();

        if (!empty($errors)) {
            $session->set_temp_data(json_encode(['errors' => $errors, 'data' => $applicationData, 'xperience' => $experienceProof], JSON_UNESCAPED_UNICODE));
            return false;
        } else {
            $saveSuccess = true;

            if (empty($id)) {
                $appExists = $this->checkAppExists($applicationData['registration_number']);

                if (!empty($appExists) && count($appExists) >= 2) {
                    $session->set_temp_data(json_encode(['errors' => ['Βρέθηκαν δυο(2) καταχωρημένες αιτήσεις με το συγκεκριμένο A.M.K.A.'], 'data' => $applicationData, 'xperience' => $experienceProof], JSON_UNESCAPED_UNICODE));
                    return false;
                }

                $d = new \DateTime('NOW', new \DateTimeZone('Europe/Athens'));
                $created_on = $d->format('Y-m-d H:i:s');
                $applicationData['created'] = $created_on;

                $applicationId = $this->saveApplication($applicationData);

                if (empty($applicationId)) {
                    $session->set_temp_data(json_encode(['errors' => ['Παρουσιάστηκε πρόβλημα κάτα την αποθήκευση της αίτησης'], 'data' => $applicationData, 'xperience' => $experienceProof], JSON_UNESCAPED_UNICODE));
                    return false;
                }

                if (!empty($applicationId) && is_array($experienceProof)) {
                    $saveExperience = $this->saveExperience($experienceProof, $applicationId);

                    if (empty($saveExperience)) {
                        $saveSuccess = false;
                    }
                }

                if (!empty($saveSuccess)) {
                    setcookie("applicationSuccess", 'Η αίτηση αποθηκεύτηκε με επιτυχία', time() + 3600, "/".ROOT_FOLDER."applications/");
                } else {
                    setcookie("applicationSuccess", 'Η αίτηση αποθηκεύτηκε με επιτυχία. Παρουσιάστηκε πρόβλημα κατά την αποθήκευση απόδειξης εμπειρίας', time() + 3600, "/".ROOT_FOLDER."applications/");
                }
            } else {
                if (!empty($appDbData['registration_number']) && !empty($applicationData['registration_number'])) {
                    if ($appDbData['registration_number'] != $applicationData['registration_number']) {
                        $appExists = $this->checkAppExists($applicationData['registration_number']);

                        if (!empty($appExists) && count($appExists) >= 2) {
                            $session->set_temp_data(json_encode(['errors' => ['Βρέθηκαν δυο(2) καταχωρημένες αιτήσεις με το συγκεκριμένο A.M.K.A.'], 'data' => $applicationData, 'xperience' => $experienceProof], JSON_UNESCAPED_UNICODE));
                            return $id;
                        }
                    }
                }

                $this->deleteExperience($id);

                $updateApplication = $this->updateApplication($applicationData, $id);

                $saveExperience = $this->saveExperience($experienceProof, $id);

                setcookie("applicationSuccess", 'Η αίτηση αποθηκεύτηκε με επιτυχία', time() + 3600, "/".ROOT_FOLDER."applications/");

                $applicationId = $id;
            }

            if (!$applicationData['not_valid']) {
                $applicationScore = new ApplicationScore($mainQualData, $addQualData);
                $rate = $applicationScore->rateApplication($applicationId, $positionData, $diplomaData, $postgradData, $applicationData, $experienceProof);
            }

            return $applicationId;
        }
    }

    public function recalculateApps(iterable $positionData, iterable $diplomaData, iterable $postgradData, iterable $mainQualData, iterable $addQualData)
    {
        $applications = $this->getApplicationList('all');

        if (!empty($applications)) {
            $applicationScore = new ApplicationScore($mainQualData, $addQualData);
            foreach ($applications as $application) {
                if (!$application['not_valid']) {
                    $experience = $this->getExperienceById($application['id']);
                    $experienceProof = !empty($experience) ? $experience : [];
                    $applicationScore->rateApplication($application['id'], $positionData, $diplomaData, $postgradData, $application, $experienceProof);
                }
            }
        }
    }
}
