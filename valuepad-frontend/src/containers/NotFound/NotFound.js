import React from 'react';
import {Link} from 'react-router';

export default function NotFound() {
  return (
    <div className="container">
      <h1>Page not found!</h1>
      <p>The page you are looking for could not be found. Please click {
        <Link to="/orders/new" style={{color: '#17A1E5'}}>
          here
        </Link>
      } to return to your dashboard.</p>
    </div>
  );
}
