import React from "react";
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Redirect,
} from "react-router-dom";
import { Navbar } from "./app/Navbar";

function AppTutrial() {
    return (
        <Router>
            <Navbar />
            <Switch>
                <Route
                    exact
                    path="/"
                    render={() => (
                        <section>
                            <h2>Welcome to Redux!!</h2>
                        </section>
                    )}
                />
                <Redirect to="/" />
            </Switch>
        </Router>
    );
}

export default AppTutrial;
