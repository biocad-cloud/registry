namespace viewer {

    // 定义数据接口
    export interface PieChartData {
        name: string;
        value: number;
    }

    export interface SpeciesData {
        [key: string]: number;
    }


    export function toPieData(rawData: SpeciesData) {
        return Object.entries(rawData).map(([name, value]) => ({
            name,
            value
        }));
    }

}