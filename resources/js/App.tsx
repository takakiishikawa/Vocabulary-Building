import React from "react";
import ReactDOM from "react-dom";
import { BrowserRouter as Router, Route, Link, Routes } from "react-router-dom";
import AdminTop from "./Admin/AdminTop";
import UserTop from "./User/UserTop";

const App: React.FC = () => {
    return (
        <Router>
            <div>
                <ul>
                    <li>
                        <Link to="/admin">Admin</Link>
                    </li>
                    <li>
                        <Link to="/user">User</Link>
                    </li>
                </ul>
                <Routes>
                    <Route path="/admin/*" element={<AdminTop />} />
                    <Route path="/user/*" element={<UserTop />} />
                </Routes>
            </div>
        </Router>
    );
};

export default App;

ReactDOM.render(<App />, document.querySelector("#root"));
