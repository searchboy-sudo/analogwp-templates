import styled from 'styled-components';

const Container = styled.div`
	text-align: center;
	
	p {
		font-size: 16px;
	}
`;

const Empty = ( { text = 'No templates found.', ...rest } ) => {
	return (
		<Container { ...rest }>
			<p>{ text }</p>
		</Container>
	);
};

export default Empty;
