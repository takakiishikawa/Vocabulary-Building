import { createSlice, PayloadAction } from "@reduxjs/toolkit";
import { RootState } from "./store";

interface technologyState {
    idCount: number;
    technologyList: { id: number; name: string }[];
    selectedTechnology: { id: number; name: string };
}

const initialState: technologyState = {
    idCount: 1,
    technologyList: [],
    selectedTechnology: { id: 0, name: "" },
};

export const technologySlice = createSlice({
    name: "technology",
    initialState,
    reducers: {
        createTechnology: (
            state,
            action: PayloadAction<{ id: number; name: string }>
        ) => {
            state.idCount++;
            const newTechnology = {
                id: state.idCount,
                name: action.payload.name,
            };
            state.technologyList = [newTechnology, ...state.technologyList];
        },
    },
});

export const { createTechnology } = technologySlice.actions;

export const selectTechnology = (state: RootState) => state.createTechnology;

export default technologySlice.reducer;
