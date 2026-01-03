export interface Race {
    id: number;
    name: string;
    date: Date;
    odds: number[];
}

export interface Odds {
    raceId: number;
    odds: number[];
}