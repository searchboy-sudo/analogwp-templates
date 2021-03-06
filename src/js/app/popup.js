import { default as styled, keyframes } from 'styled-components';
import Close from './icons/close';
const { __ } = wp.i18n;

const animate = keyframes`
  from {
    margin-top: 50px;
  }

  to {
    margin-top: 0;
  }
`;

const Container = styled.div`
	font-size: 15px;
	font-weight: 500;
	color: #6d6d6d;
	font-family: 'Poppins', sans-serif;
	position: fixed;
    top: 0;
    left: 0;
    background: rgba(0,0,0,0.62);
    width: 100%;
    height: 100%;
    z-index: 10000;
	display: flex;
    align-items: center;
    justify-content: center;

	.inner {
		background: #fff;
		border-radius: 4px;
		overflow: scroll;
		width: 600px;
		max-height: 80vh;
		animation-fill-mode: forwards;
		animation: ${ animate } 0.1s ease-out;
	}

	p {
		font-size: inherit;
	}

	a {
		color: #3152FF;
		text-decoration: none;
	}
`;

const Header = styled.div`
	margin: 0 45px;
	padding: 20px 0;
	border-bottom: 2px solid #F1F1F1;
	display: flex;
	justify-content: space-between;
	align-items: center;
	position: sticky;
    top: 0;
    background: #fff;

	h1 {
		font-size: 13px;
		font-weight: bold;
		color: #060606;
		margin: 0;
	}

	svg {
		fill: #000;
		margin-left: 5px;
	}

	button {
		font-size: 12px !important;
	}
`;

const Content = styled.div`
	margin: 0;
	padding: 20px 45px;
	font-size: 14.22px;

	h2 {
		font-size: 25px;
	}

	hr {
		border-top: 2px solid #F1F1F1;
		border-bottom: none;
		margin: 2em 0;
	}

	.form-row {
		display: flex;
		align-items: center;
	}

	.components-base-control {
		flex-basis: 65%;
	}

	.components-base-control__field {
		margin: 0;
	}

	input[type="text"] {
		border: 1px solid #E9E9E9;
		background: #F3F3F3;
		box-shadow: none;
		outline: 0;
		padding: 10px 20px;
		max-width: 100% !important;
		border-right: 0;
	}
`;

const Popup = ( props ) => {
	const { title, onRequestClose, children, ...rest } = props;
	return (
		<Container { ...rest }>
			<div className="inner">
				<Header>
					<h1>{ title }</h1>
					<button className="button-plain" onClick={ () => onRequestClose() }>
						{ __( 'Close', 'ang' ) } <Close />
					</button>
				</Header>

				<Content>
					{ children }
				</Content>
			</div>
		</Container>
	);
};

export default Popup;
