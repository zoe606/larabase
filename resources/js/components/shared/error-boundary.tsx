import { Button } from '@/components/ui/button';
import * as Sentry from '@sentry/react';
import { AlertTriangle, RefreshCw } from 'lucide-react';
import { Component, type ErrorInfo, type ReactNode } from 'react';

interface Props {
    children: ReactNode;
    fallback?: ReactNode;
}

interface State {
    hasError: boolean;
    error: Error | null;
    errorInfo: ErrorInfo | null;
}

export class ErrorBoundary extends Component<Props, State> {
    constructor(props: Props) {
        super(props);
        this.state = {
            hasError: false,
            error: null,
            errorInfo: null,
        };
    }

    static getDerivedStateFromError(error: Error): Partial<State> {
        return { hasError: true, error };
    }

    componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
        this.setState({ errorInfo });

        // Log error to console in development
        if (import.meta.env.DEV) {
            console.error('ErrorBoundary caught an error:', error, errorInfo);
        }

        // Send to Sentry in production
        if (import.meta.env.PROD) {
            Sentry.captureException(error, {
                extra: { componentStack: errorInfo.componentStack },
            });
        }
    }

    handleReset = (): void => {
        this.setState({
            hasError: false,
            error: null,
            errorInfo: null,
        });
    };

    handleReload = (): void => {
        window.location.reload();
    };

    render(): ReactNode {
        if (this.state.hasError) {
            if (this.props.fallback) {
                return this.props.fallback;
            }

            return (
                <div className="flex min-h-[400px] flex-col items-center justify-center p-8">
                    <div className="bg-destructive/10 mb-4 rounded-full p-4">
                        <AlertTriangle className="text-destructive h-8 w-8" />
                    </div>
                    <h2 className="mb-2 text-xl font-semibold">Something went wrong</h2>
                    <p className="text-muted-foreground mb-6 max-w-md text-center text-sm">
                        An unexpected error occurred. Please try refreshing the page or contact support if the problem persists.
                    </p>

                    {import.meta.env.DEV && this.state.error && (
                        <div className="bg-muted mb-6 max-w-2xl overflow-auto rounded-md p-4">
                            <p className="font-mono text-sm text-red-600">{this.state.error.toString()}</p>
                            {this.state.errorInfo && (
                                <pre className="text-muted-foreground mt-2 text-xs whitespace-pre-wrap">{this.state.errorInfo.componentStack}</pre>
                            )}
                        </div>
                    )}

                    <div className="flex gap-3">
                        <Button variant="outline" onClick={this.handleReset}>
                            Try Again
                        </Button>
                        <Button onClick={this.handleReload}>
                            <RefreshCw className="mr-2 h-4 w-4" />
                            Reload Page
                        </Button>
                    </div>
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
