import React, {Component, PropTypes} from 'react';
import Immutable from 'immutable';
import {
    GoogleMapLoader,
    GoogleMap,
    InfoWindow,
    Marker
} from 'react-google-maps';

const styles = {
  map: { width: '100%', height: '500px' }
};

export default class AddressGoogleMap extends Component {
  static propTypes = {
    marker: PropTypes.instanceOf(Immutable.Map),
    fetchMarkerAddress: PropTypes.func.isRequired,
    selectedRecord: PropTypes.instanceOf(Immutable.Map),
    orders: PropTypes.instanceOf(Immutable.Map),
  };

  constructor(props) {
    super(props);

    this.state = {
      infoWindowOpen: true,
      map: null
    };

    this.markerClick = ::this.markerClick;
    this.toggleInfoWindow = ::this.toggleInfoWindow;
  }

  markerClick() {
    this.toggleInfoWindow();
  }

  toggleInfoWindow() {
    this.setState({
      infoWindowOpen: !this.state.infoWindowOpen
    });
  }

  render() {
    const {marker} = this.props;
    // if the fetch failed lets display that
    if (marker.get('fetchFailed')) {
      return (
        <div className="details-cont">
          <div className="row"><div className="col-md-12">The address could not be found.</div></div>
        </div>
      );
    }

    if (marker.get('fetching') === undefined || marker.get('fetching')) {
      return (
        <div className="details-cont">
          <div className="row"><div className="col-md-12">Loading map.</div></div>
        </div>
      );
    }

    const markerPosition = marker.get('position');

    return (
      <div className="details-cont">
        <div className="row">
          <GoogleMapLoader
            containerElement={
              <div {...this.props} style={styles.map}></div>
            }
            googleMapElement={
              <GoogleMap center={markerPosition} defaultZoom={14}>
                <Marker position={markerPosition} onClick={this.markerClick}>
                  {this.state.infoWindowOpen &&
                   <InfoWindow onCloseclick={this.toggleInfoWindow}>
                     <div>
                       <div>{marker.get('address1', '')} {marker.get('address2', '')}</div>
                       <div>{marker.get('city', '')}. {marker.getIn(['state', 'code'], '')}, {marker.get('zip', '')}</div>
                     </div>
                   </InfoWindow>
                  }
                </Marker>
              </GoogleMap>
            }
          />
        </div>
      </div>
    );
  }
}
