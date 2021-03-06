import { requestSettingUpdate } from './api';
import Filters from './filters';
import Settings from './settings/Settings';
import StyleKits from './stylekits/stylekits';
import Templates from './Templates';
const { Fragment } = React;

export const getPageComponents = ( state ) => {
	if ( state.tab === 'settings' ) {
		return <Settings />;
	}
	if ( state.tab === 'stylekits' ) {
		return <StyleKits />;
	}

	return (
		<Fragment>
			{ ! state.isOpen && <Filters /> }
			<Templates />
		</Fragment>
	);
};

export const debugMode = () => Boolean( AGWP.debugMode );

export const Log = ( what ) => {
	if ( debugMode ) {
		console.log( what ); // eslint-disable-line
	}
};
export const LogStart = ( title ) => {
	if ( debugMode ) {
		console.group( title ); // eslint-disable-line
	}
};
export const LogEnd = ( title ) => {
	if ( debugMode ) {
		console.groupEnd( title ); // eslint-disable-line
	}
};

export function generateUEID() {
	let first = ( Math.random() * 46656 ) || 0;
	let second = ( Math.random() * 46656 ) || 0;
	first = ( '000' + first.toString( 36 ) ).slice( -3 );
	second = ( '000' + second.toString( 36 ) ).slice( -3 );
	return first + second;
}

export function hasProTemplates( templates ) {
	const filtered = templates.some( ( template ) => template.is_pro === true );

	return filtered;
}

export function increaseInstallCount( settings, dispatch ) {
	const installCount = parseInt( settings.install_count ) || 0;

	dispatch( {
		settings: {
			...settings,
			install_count: installCount + 1,
		},
	} );

	requestSettingUpdate( 'install_count', installCount + 1 );
}

export function isNewTheme( date ) {
	const start = moment.unix( date );
	const end = moment.now();
	const diff = Math.ceil( moment.duration( start.diff( end ) ).asDays() );

	return diff;
}
