<?php

namespace DTApi\Http\Controllers;

use App\Models\Job;
use App\Http\Requests;
use App\Models\Distance;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use Illuminate\Http\Response;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{

    /**
     * @var BookingRepository
     */
    protected BookingRepository $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function index(Request $request): Response|Application|ResponseFactory
    {
        $response = [];
        [$data, $auth_user] = $this->getDataAndAuthUser($request);
        $data['auth_user'] = $auth_user;
        $user_type = $auth_user->user_type;
        if($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($user_id);
        } elseif($user_type == env('ADMIN_ROLE_ID') || $user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($data);
        }
        return response($response);
    }

    /**
     * @param $id
     * @return Application|Response|ResponseFactory
     */
    public function show($id): Response|Application|ResponseFactory
    {
        return response($this->repository->with('translatorJobRel.user')->findOrFail($id));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function store(Request $request): Response|Application|ResponseFactory
    {
        [$data, $auth_user] = $this->getDataAndAuthUser($request);
        return response($this->repository->store($auth_user, $data));
    }

    /**
     * @param $id
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function update($id, Request $request): Response|Application|ResponseFactory
    {
        [$data, $auth_user] = $this->getDataAndAuthUser($request);
        return response($this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $auth_user));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function immediateJobEmail(Request $request): Response|Application|ResponseFactory
    {
        //$adminSenderEmail = config('app.adminemail'); ## Defined but no used anywhere
        return response($this->repository->storeJobEmail($request->all()));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory|null
     */
    public function getHistory(Request $request): Response|Application|ResponseFactory|null
    {
        if($auth_user_id = $request->get('user_id')) {
            return response($this->repository->getUsersJobsHistory($auth_user_id, $request));
        }
        return null;
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function acceptJob(Request $request): Response|Application|ResponseFactory
    {
        [$data, $auth_user] = $this->getDataAndAuthUser($request);
        return response($this->repository->acceptJob($data, $auth_user));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function acceptJobWithId(Request $request): Response|Application|ResponseFactory
    {
        [$data, $auth_user] = $this->getDataAndAuthUser($request);
        return response($this->repository->acceptJobWithId($data['job_id'], $auth_user));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function cancelJob(Request $request): Response|Application|ResponseFactory
    {
        [$data, $auth_user] = $this->getDataAndAuthUser($request);
        return response($this->repository->cancelJobAjax($data, $auth_user));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function endJob(Request $request): Response|Application|ResponseFactory
    {
        return response($this->repository->endJob($request->all()));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function customerNotCall(Request $request): Response|Application|ResponseFactory
    {
        return response($this->repository->customerNotCall($request->all()));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function getPotentialJobs(Request $request): Response|Application|ResponseFactory
    {
        //$data = $request->all(); ## not required here
        return response($this->repository->getPotentialJobs($request->__authenticatedUser));
    }

    /**
     * @param Request $request
     * @return Response|string
     */
    public function distanceFeed(Request $request): Response|string
    {
        $data = $request->all();
        $distance = (isset($data['distance']) && $data['distance'] != "") ? $data['distance'] : "";
        $time = (isset($data['time']) && $data['time'] != "") ? $data['time'] : "";
        $jobid = (isset($data['jobid']) && $data['jobid'] != "") ? $data['jobid'] : "";
        $session = (isset($data['session_time']) && $data['session_time'] != "") ? $data['session_time'] : "";

        $flagged = 'no';
        if ($data['flagged'] == 'true') {
            if($data['admincomment'] == '') {
                return "Please, add comment";
            }
            $flagged = 'yes';
        }

        $manually_handled = ($data['manually_handled'] == 'true') ? 'yes' : 'no';
        $by_admin = ($data['by_admin'] == 'true') ? 'yes' : 'no';
        $admincomment = (isset($data['admincomment']) && $data['admincomment'] != "") ? $data['admincomment'] : "";

        if ($time || $distance) {
            Distance::where('job_id', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', $jobid)->update([
                'admin_comments' => $admincomment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin
            ]);
        }

        return response('Record updated!');
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function reopen(Request $request): Response|Application|ResponseFactory
    {
        return response($this->repository->reopen($request->all()));
    }

    /**
     * @param Request $request
     * @return Application|Response|ResponseFactory
     */
    public function resendNotifications(Request $request): Response|Application|ResponseFactory
    {
        $jobid = $request->jobid ?? null;
        $job = $this->repository->findOrFail($jobid);
        $this->repository->sendNotificationTranslator($job, $this->repository->jobToData($job), '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return ResponseFactory|Response
     */
    public function resendSMSNotifications(Request $request): Response|ResponseFactory
    {
        $jobid = $request->jobid ?? null;
        try {
            $job = $this->repository->findOrFail($jobid);
            //$job_data = $this->repository->jobToData($job); //commented due to this not used anywhere in current function.
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getDataAndAuthUser(Request $request): array
    {
        return [$request->all(), $request->__authenticatedUser];
    }
}