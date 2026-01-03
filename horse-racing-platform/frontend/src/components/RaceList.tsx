import React, { useEffect, useState } from 'react';
import axios from 'axios';

const RaceList: React.FC = () => {
    const [races, setRaces] = useState([]);

    useEffect(() => {
        const fetchRaces = async () => {
            try {
                const response = await axios.get('/api/races');
                setRaces(response.data);
            } catch (error) {
                console.error('Error fetching races:', error);
            }
        };

        fetchRaces();
    }, []);

    return (
        <div>
            <h1>Race List</h1>
            <ul>
                {races.map((race) => (
                    <li key={race.id}>
                        {race.name} - {new Date(race.date).toLocaleDateString()}
                    </li>
                ))}
            </ul>
        </div>
    );
};

export default RaceList;