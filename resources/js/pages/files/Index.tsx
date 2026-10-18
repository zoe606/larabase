import { DeleteConfirmation, LoadingButton } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import {
    ChevronRight,
    Download,
    File,
    FileArchive,
    FileAudio,
    FileImage,
    FileText,
    FileVideo,
    Folder,
    FolderPlus,
    FolderRoot,
    Trash2,
    UploadCloud,
} from 'lucide-react';
import React, { useRef, useState } from 'react';
import { toast } from 'sonner';

interface FolderNode {
    id: number;
    name: string;
    parent_id: number | null;
    children: FolderNode[];
}

interface FileNode {
    id: number;
    name: string;
    mime_type: string;
    url: string;
    size: string;
    created_at: string;
}

interface Props {
    folders: FolderNode[];
    currentFolderId: number | null;
    currentFolder: FolderNode | null;
    files: FileNode[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'File Management',
        href: '/files',
    },
];

function buildFolderTree(flat: FolderNode[]): (FolderNode & { children: FolderNode[] })[] {
    const map = new Map<number, FolderNode & { children: FolderNode[] }>();
    const roots: (FolderNode & { children: FolderNode[] })[] = [];

    flat.forEach((folder) => {
        map.set(folder.id, { ...folder, children: [] });
    });

    flat.forEach((folder) => {
        if (folder.parent_id) {
            const parent = map.get(folder.parent_id);
            if (parent) {
                parent.children.push(map.get(folder.id)!);
            }
        } else {
            roots.push(map.get(folder.id)!);
        }
    });

    return roots;
}

function getFileIcon(mime: string) {
    if (mime.startsWith('image/')) return <FileImage className="text-info h-5 w-5" />;
    if (mime.startsWith('video/')) return <FileVideo className="text-tertiary h-5 w-5" />;
    if (mime.startsWith('audio/')) return <FileAudio className="text-tertiary h-5 w-5" />;
    if (mime.includes('zip') || mime.includes('rar') || mime.includes('tar') || mime.includes('7z'))
        return <FileArchive className="text-warning h-5 w-5" />;
    if (mime.includes('pdf')) return <FileText className="text-error h-5 w-5" />;
    if (mime.includes('text') || mime.includes('csv') || mime.includes('json')) return <FileText className="text-success h-5 w-5" />;
    if (mime.includes('word')) return <FileText className="text-info h-5 w-5" />;
    if (mime.includes('excel') || mime.includes('sheet')) return <FileText className="text-success h-5 w-5" />;
    if (mime.includes('powerpoint') || mime.includes('presentation')) return <FileText className="text-warning h-5 w-5" />;
    return <File className="text-on-surface-variant h-5 w-5" />;
}

function isPreviewable(mime: string) {
    return mime.startsWith('image/') || mime.startsWith('text/') || mime.includes('pdf') || mime.includes('document');
}

export default function FileManager({ folders, currentFolderId, currentFolder, files }: Props) {
    const fileInputRef = useRef<HTMLInputElement | null>(null);
    const [uploading, setUploading] = useState(false);
    const [newFolderName, setNewFolderName] = useState('');
    const [previewFile, setPreviewFile] = useState<FileNode | null>(null);
    const [isCreatingFolder, setIsCreatingFolder] = useState(false);
    const [isSavingFolder, setIsSavingFolder] = useState(false);

    const handleDeleteFolder = (folderId: number) => {
        const deletingCurrent = folderId === currentFolderId;

        router.delete(`/media/${folderId}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Folder deleted successfully');

                if (deletingCurrent) {
                    router.visit('/files', { replace: true });
                } else {
                    router.reload({ only: ['folders'] });
                }
            },
            onError: () => toast.error('Failed to delete folder'),
        });
    };

    const handleUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
        const files = e.target.files;
        if (!files || files.length === 0) return;

        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (file) formData.append('files[]', file);
        }
        if (currentFolderId) formData.append('folder_id', currentFolderId.toString());

        setUploading(true);
        router.post('/files', formData, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                toast.success('File uploaded successfully');
                if (fileInputRef.current) fileInputRef.current.value = '';
                router.reload({ only: ['files'] });
            },
            onError: () => toast.error('Failed to upload file'),
            onFinish: () => setUploading(false),
        });
    };

    const handleCreateFolder = () => {
        if (!newFolderName.trim()) {
            toast.error('Folder name cannot be empty');
            return;
        }

        setIsSavingFolder(true);
        router.post(
            '/media',
            {
                name: newFolderName,
                parent_id: currentFolderId,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Folder created successfully');
                    setNewFolderName('');
                    setIsCreatingFolder(false);
                    router.reload({ only: ['folders'] });
                },
                onError: () => {
                    toast.error('Failed to create folder');
                },
                onFinish: () => setIsSavingFolder(false),
            },
        );
    };

    const handleDeleteFile = (fileId: number) => {
        router.delete(`/files/${fileId}`, {
            preserveScroll: true,
            onSuccess: () => {
                setPreviewFile(null);
                router.reload({ only: ['files'] });
            },
        });
    };

    const renderFolderTree = (nodes: (FolderNode & { children: FolderNode[] })[], level = 0) => {
        return nodes.map((folder) => (
            <div key={folder.id} style={{ marginLeft: level * 12 }} className="mb-1">
                <div
                    className={`hover:bg-surface-container flex cursor-pointer items-center gap-1 rounded p-1 ${currentFolderId === folder.id ? 'bg-surface-container' : ''}`}
                    onClick={() => router.visit(`/files?folder_id=${folder.id}`)}
                >
                    {folder.children.length > 0 && <ChevronRight className="text-on-surface-variant h-4 w-4" />}
                    {folder.children.length === 0 && <span className="h-4 w-4"></span>}

                    <Folder className="text-warning h-4 w-4" />
                    <span className="flex-1 truncate text-sm">{folder.name}</span>

                    <DeleteConfirmation
                        title="Delete Folder?"
                        description={`Folder "${folder.name}" and all its contents will be permanently deleted.`}
                        onConfirm={() => handleDeleteFolder(folder.id)}
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            className="text-on-surface-variant hover:text-error h-6 w-6"
                            onClick={(e) => e.stopPropagation()}
                        >
                            <Trash2 className="h-3 w-3" />
                        </Button>
                    </DeleteConfirmation>
                </div>
                {folder.children.length > 0 && <div className="ml-4">{renderFolderTree(folder.children, level + 1)}</div>}
            </div>
        ));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="File Manager" />
            <div className="grid flex-1 grid-cols-1 gap-6 p-4 md:grid-cols-4 md:p-6">
                {/* Sidebar Folder Tree */}
                <div className="bg-surface-container-lowest rounded-lg border p-4 md:col-span-1">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-sm font-semibold">Folder Structure</h2>
                        <Button variant="outline" size="sm" onClick={() => setIsCreatingFolder(true)} className="gap-1">
                            <FolderPlus className="h-4 w-4" />
                            <span>New Folder</span>
                        </Button>
                    </div>

                    <div className="max-h-[calc(100vh-250px)] overflow-y-auto">
                        <div
                            className={`hover:bg-surface-container mb-2 flex cursor-pointer items-center gap-2 rounded p-2 ${!currentFolderId ? 'bg-surface-container' : ''}`}
                            onClick={() => router.visit('/files')}
                        >
                            <FolderRoot className="text-info h-4 w-4" />
                            <span className="text-sm font-medium">Root Folder</span>
                        </div>
                        {renderFolderTree(buildFolderTree(folders ?? []))}
                    </div>
                </div>

                {/* Main Panel File View */}
                <div className="space-y-4 md:col-span-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            <h2 className="text-lg font-semibold">{currentFolder ? currentFolder.name : 'Root Folder'}</h2>
                            {currentFolder && (
                                <span className="text-on-surface-variant text-sm">
                                    ({files.length} {files.length === 1 ? 'item' : 'items'})
                                </span>
                            )}
                        </div>
                        <div className="flex items-center gap-2">
                            <Input ref={fileInputRef} type="file" onChange={handleUpload} disabled={uploading} className="hidden" multiple />
                            <LoadingButton onClick={() => fileInputRef.current?.click()} loading={uploading}>
                                <UploadCloud className="mr-2 h-4 w-4" />
                                Upload
                            </LoadingButton>
                        </div>
                    </div>

                    <Separator />

                    {files.length === 0 ? (
                        <div className="text-on-surface-variant flex h-64 flex-col items-center justify-center">
                            <Folder className="mb-2 h-12 w-12" />
                            <p>No files in this folder</p>
                            <Button variant="ghost" size="sm" className="mt-2" onClick={() => fileInputRef.current?.click()}>
                                Upload the first file
                            </Button>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {files.map((file) => (
                                <div
                                    key={file.id}
                                    className="group hover:border-info cursor-pointer rounded-lg border p-4 transition-colors"
                                    onClick={() => (isPreviewable(file.mime_type) ? setPreviewFile(file) : null)}
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="flex items-center gap-3">
                                            {getFileIcon(file.mime_type)}
                                            <div className="overflow-hidden">
                                                <p className="truncate font-medium">{file.name}</p>
                                                <p className="text-on-surface-variant text-xs">{file.size}</p>
                                                <p className="text-on-surface-variant text-xs">{file.created_at}</p>
                                            </div>
                                        </div>
                                        <div className="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                            <a
                                                href={file.url}
                                                download={file.name}
                                                onClick={(e) => e.stopPropagation()}
                                                className="text-on-surface-variant hover:text-info p-1"
                                            >
                                                <Download className="h-4 w-4" />
                                            </a>
                                            <DeleteConfirmation
                                                title="Delete File?"
                                                description={`File "${file.name}" will be permanently deleted.`}
                                                onConfirm={() => handleDeleteFile(file.id)}
                                            >
                                                <button className="text-on-surface-variant hover:text-error p-1" onClick={(e) => e.stopPropagation()}>
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </DeleteConfirmation>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {/* Create Folder Dialog */}
            <Dialog open={isCreatingFolder} onOpenChange={setIsCreatingFolder}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Create New Folder</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-4">
                        <Input
                            placeholder="Folder name"
                            value={newFolderName}
                            onChange={(e) => setNewFolderName(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && handleCreateFolder()}
                        />
                        <div className="flex justify-end gap-2">
                            <Button variant="outline" onClick={() => setIsCreatingFolder(false)}>
                                Cancel
                            </Button>
                            <LoadingButton onClick={handleCreateFolder} loading={isSavingFolder} disabled={!newFolderName.trim()}>
                                Create
                            </LoadingButton>
                        </div>
                    </div>
                </DialogContent>
            </Dialog>

            {/* File Preview Dialog */}
            <Dialog open={!!previewFile} onOpenChange={(open) => !open && setPreviewFile(null)}>
                <DialogContent className="flex max-h-[90vh] max-w-4xl flex-col">
                    {previewFile && (
                        <>
                            <DialogHeader>
                                <DialogTitle className="truncate">{previewFile.name}</DialogTitle>
                            </DialogHeader>
                            <div className="flex-1 overflow-auto p-4">
                                {previewFile.mime_type.startsWith('image/') ? (
                                    <img src={previewFile.url} alt={previewFile.name} className="mx-auto max-h-[70vh] max-w-full object-contain" />
                                ) : previewFile.mime_type.includes('pdf') ? (
                                    <iframe src={previewFile.url} className="h-[70vh] w-full rounded border" title={previewFile.name} />
                                ) : previewFile.mime_type.startsWith('text/') ? (
                                    <div className="bg-surface-container h-[70vh] overflow-auto rounded p-4">
                                        <pre className="whitespace-pre-wrap">{/* Content would be loaded here */}</pre>
                                    </div>
                                ) : (
                                    <div className="text-on-surface-variant flex h-64 flex-col items-center justify-center">
                                        <File className="mb-2 h-12 w-12" />
                                        <p>Preview not available</p>
                                        <a href={previewFile.url} download={previewFile.name} className="text-info mt-2 hover:underline">
                                            Download file
                                        </a>
                                    </div>
                                )}
                            </div>
                        </>
                    )}
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
