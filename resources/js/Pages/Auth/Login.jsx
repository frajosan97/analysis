import { Form, Button, Container, Row, Col, Card, Alert, Stack } from 'react-bootstrap';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Log in" />

            <Container className="py-4">
                <Row className="justify-content-center">
                    <Col md={8} lg={6}>
                        <Card className="p-4">
                            <Card.Body>
                                {status && (
                                    <Alert variant="success" className="mb-4">
                                        {status}
                                    </Alert>
                                )}

                                <Form onSubmit={submit}>
                                    <Form.Group controlId="email" className="mb-3">
                                        <Form.Label>Email</Form.Label>
                                        <Form.Control
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            autoComplete="username"
                                            autoFocus
                                            isInvalid={!!errors.email}
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {errors.email}
                                        </Form.Control.Feedback>
                                    </Form.Group>

                                    <Form.Group controlId="password" className="mb-3">
                                        <Form.Label>Password</Form.Label>
                                        <Form.Control
                                            type="password"
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            autoComplete="current-password"
                                            isInvalid={!!errors.password}
                                        />
                                        <Form.Control.Feedback type="invalid">
                                            {errors.password}
                                        </Form.Control.Feedback>
                                    </Form.Group>

                                    <Form.Group controlId="remember" className="mb-3">
                                        <Form.Check
                                            type="checkbox"
                                            label="Remember me"
                                            checked={data.remember}
                                            onChange={(e) => setData('remember', e.target.checked)}
                                        />
                                    </Form.Group>

                                    <Stack direction="horizontal" gap={3} className="justify-content-between">
                                        {canResetPassword && (
                                            <Link
                                                href={route('password.request')}
                                                className="text-decoration-none"
                                            >
                                                Forgot your password?
                                            </Link>
                                        )}
                                        <Button
                                            variant="primary"
                                            type="submit"
                                            disabled={processing}
                                            className="ms-auto"
                                        >
                                            {processing ? 'Logging in...' : 'Log in'}
                                        </Button>
                                    </Stack>
                                </Form>
                            </Card.Body>
                        </Card>
                    </Col>
                </Row>
            </Container>
        </GuestLayout>
    );
}