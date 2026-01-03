export interface Race {
    id: number;
    name: string;
    date: string;
    odds: number[];
}

export interface Odds {
    raceId: number;
    odds: number[];
}