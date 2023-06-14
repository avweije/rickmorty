<?php
    namespace App\Utils;
    
    /**
     * RickMortyService
     */
    class RickMortyService
    {
        // the base url for the Rick and Morty API
        protected string $baseUrl = 'https://rickandmortyapi.com/api/';
        
        /**
         * getLocations - returns an array with all locations
         *
         * @return array
         */
        public function getLocations(): array
        {
            return $this->getData('location');
        }
        
        /**
         * getEpisodes - returns an array with all episodes
         *
         * @return array
         */
        public function getEpisodes(): array
        {
            return $this->getData('episode');
        }
        
        /**
         * getCharacters - returns an array with all characters
         *
         * @return array
         */
        public function getCharacters(): array
        {
            return $this->getData('character');
        }

        /**
         * getData - fetches data from the API
         *
         * @param  mixed $type
         * @return array
         */
        private function getData(string $type): array
        {
            // set the url
            $url = $this->baseUrl.$type;
            //  initialise the curl call
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            // the results array
            $results = [];
            // keep fetching pages until we have all the data
            while ($url !== '') 
            {
                // set the url (for the next page)
                curl_setopt($ch, CURLOPT_URL, $url);
                // get the data
                $data = curl_exec($ch);
                // decode to an array
                $temp = json_decode($data, true);

                // add to the results
                $results = array_merge($results, $temp['results']);

                // set the next url (if any)
                $url = isset($temp['info']) && isset($temp['info']['next']) ? $temp['info']['next'] : '';
            }

            // close it
            curl_close($ch);

            return $results;
        }
    }