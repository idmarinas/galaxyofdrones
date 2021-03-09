<?php

namespace App\Http\Controllers\Api;

use App\Events\PlanetUpdated;
use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Grid;
use App\Models\Planet;
use App\Models\User;
use App\Transformers\PlanetAllTransformer;
use App\Transformers\PlanetShowTransformer;
use App\Transformers\PlanetTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PlanetController extends Controller
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('verified');
        $this->middleware('player');
    }

    /**
     * Show the current planet in json format.
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(PlanetTransformer $transformer)
    {
        return $transformer->transform(
            auth()->user()->current
        );
    }

    /**
     * Show the all planet in json format.
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function all(User $user, PlanetAllTransformer $transformer)
    {
        return $transformer->transformCollection(
            $user->paginatePlanets()
        );
    }

    /**
     * Show the capital planet in json format.
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function capital(PlanetShowTransformer $transformer)
    {
        return $transformer->transform(
            auth()->user()->capital
        );
    }

    /**
     * Show the planet in json format.
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show(Planet $planet, PlanetShowTransformer $transformer)
    {
        return $transformer->transform($planet);
    }

    /**
     * Update the current name.
     *
     * @return mixed|\Illuminate\Http\Response
     */
    public function updateName(Request $request)
    {
        if (! $request->has('name')) {
            throw new BadRequestHttpException();
        }

        $name = strip_tags(
            $request->get('name')
        );

        auth()->user()->current->update([
            'custom_name' => $name,
        ]);
    }

    /**
     * Demolish the building from the grid.
     *
     * @throws \Exception|\Throwable
     *
     * @return mixed|\Illuminate\Http\Response
     */
    public function demolish(Grid $grid)
    {
        $this->authorize('friendly', $grid->planet);

        if (! $grid->building_id) {
            throw new BadRequestHttpException();
        }

        if ($grid->upgrade) {
            throw new BadRequestHttpException();
        }

        if ($grid->training) {
            throw new BadRequestHttpException();
        }

        if ($grid->planet->isCapital() && $grid->building->type == Building::TYPE_CENTRAL) {
            throw new BadRequestHttpException();
        }

        DB::transaction(function () use ($grid) {
            $grid->demolishBuilding();

            if ($grid->building->type != Building::TYPE_CENTRAL) {
                event(
                    new PlanetUpdated($grid->planet_id)
                );
            }
        });
    }
}
