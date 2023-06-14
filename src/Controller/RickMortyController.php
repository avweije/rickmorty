<?php

namespace App\Controller;

use App\Utils\RickMortyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RickMortyController extends AbstractController
{
    #[Route('/rick/morty/{character}', name: 'app_rick_morty')]
    
        
    /**
     * index
     *
     * @param  mixed $rmservice
     * @param  mixed $request
     * @return Response
     */
    public function index(RickMortyService $rmservice, Request $request): Response
    {
        // instantiate the cache
        $cache = new FilesystemAdapter();

        // get the locations
        $locations = $cache->get('locations', function (ItemInterface $item) use ($rmservice) {
            $item->expiresAfter(3600);

            // get the locations from the API
            $data = $rmservice->getLocations();
        
            return $data;
        });

        // sort the locations by name
        usort($locations, function($a, $b) {
            if ($a['name'] < $b['name']) return -1;
            if ($a['name'] > $b['name']) return 1;
            return 0;
        });

        // get the unique dimensions
        $dimensions = [];
        foreach ($locations as $item)
        {
            if (isset($item['dimension']) && $item['dimension'] !== '')
            {
                // find the dimension
                $key = array_search($item['dimension'], array_column($dimensions, 'name'));
                if ($key !== false)
                {
                    $dimensions[$key]['locations'][] = $item['url'];
                }
                else
                {
                    $dimensions[] = ['name' => $item['dimension'], 'locations' => [$item['url']]];
                }
            }
        }

        // sort the dimensions array
        sort($dimensions);

        // get the episodes
        $episodes = $cache->get('episodes', function (ItemInterface $item) use ($rmservice) {
            $item->expiresAfter(3600);

            // get the episodes from the API
            $data = $rmservice->getEpisodes();
        
            return $data;
        });

        // render the correct template
        if ($request->isXmlHttpRequest())
        {
            // get the characters
            $characters = $cache->get('characters', function (ItemInterface $item) use ($rmservice) {
                $item->expiresAfter(3600);

                // get the characters from the API
                $data = $rmservice->getCharacters();
        
                return $data;
            });

            // the filtered characters
            $filtered = [];

            // get the parameters
            $params = $request->request->all();

            // if we need the characters for an episode
            if (isset($params['episode']) && $params['episode'] !== '')
            {
                // find the episode
                $key = array_search($params['episode'], array_column($episodes, 'id'));
                if ($key !== false)
                {
                    // get the episode
                    $episode = $episodes[$key];

                    $filtered = array_filter($characters, function ($item) use ($episode) {
                        return in_array($item['url'], $episode['characters']);
                    });
                }
            }
            else if (isset($params['location']) && $params['location'] !== '')
            {
                // find the location
                $key = array_search($params['location'], array_column($locations, 'id'));
                if ($key !== false)
                {
                    // get the location
                    $location = $locations[$key];

                    // filter the characters
                    $filtered = array_filter($characters, function ($item) use ($location) {
                        return $item['origin']['url'] == $location['url'] || $item['location']['url'] == $location['url'];
                    });
                }
            }
            else if (isset($params['dimension']) && $params['dimension'] !== '')
            {
                // find the dimension
                $key = array_search($params['dimension'], array_column($dimensions, 'name'));
                if ($key !== false)
                {
                    // get the dimension
                    $dimension = $dimensions[$key];

                    // filter the characters
                    $filtered = array_filter($characters, function ($item) use ($dimension) {
                        return in_array($item['origin']['url'], $dimension['locations']) || in_array($item['location']['url'], $dimension['locations']);
                    });
                }
            }

            // sort the characters by name
            usort($filtered, function($a, $b) {
                if ($a['name'] < $b['name']) return -1;
                if ($a['name'] > $b['name']) return 1;
                return 0;
            });

            return $this->render('rick_morty/list.html.twig', [
                'controller_name' => 'RickMortyController',
                'characters' => $filtered,
            ]);
        } 
        else
        {
            return $this->render('rick_morty/index.html.twig', [
                'controller_name' => 'RickMortyController',
                'episodes' => $episodes,
                'locations' => $locations,
                'dimensions'=> $dimensions,
            ]);
        }
    	
    }
}
