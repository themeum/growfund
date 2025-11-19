import { ExternalLink, FileCode2, HeartHandshake } from 'lucide-react';
import { Highlight, themes } from 'prism-react-renderer';
import { useEffect, useMemo, useState } from 'react';
import { useRouteError } from 'react-router';
import * as sourceMap from 'source-map-js';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface ErrorResponse {
  message?: string;
  stack?: string;
  statusText?: string;
  data?: string;
  error?: Error;
  internal?: boolean;
  status?: number;
  // Add more properties as needed
}

interface StackObject {
  file: string;
  column: number;
  line: number;
  method: string;
  source_content: string;
  start_line: number;
  end_line: number;
  source_content_lines: string[];
}

const getRelativePath = (path: string) => {
  return path.replace(/.*\/src\//, '').replace(/\?.*$/, '');
};

const errorStackToArray = (stack: string) => {
  return stack.split('\n').map((line) => {
    return line.replace(/^\s*at\s+/, '').trim();
  });
};

const parseIndividualErrorStack = async (source: string) => {
  if (!source) {
    return {
      method: '<anonymous>',
      file: '',
      line: 0,
      column: 0,
      source_content: '',
      start_line: 0,
      end_line: 0,
      source_content_lines: [],
    } as StackObject;
  }

  const [method] = /(.*?)\s+(.*?)/.exec(source) ?? [];
  const regex = /\(.*?\)$/.test(source) ? /\((.*?):(\d+):(\d+)\)$/ : /(.*?):(\d+):(\d+)$/;
  const match = regex.exec(source);
  const methodName = method ?? '<anonymous>';

  if (!match)
    return {
      method: methodName,
      file: '',
      line: 0,
      column: 0,
      source_content: '',
      start_line: 0,
      end_line: 0,
      source_content_lines: [],
    } as StackObject;
  const [, file, line, column] = match;
  const [fileWithoutQuery] = file.split('?');

  const response = await fetch(`${fileWithoutQuery}.map`);

  if (!response.ok) {
    return {
      method: methodName,
      file: getRelativePath(fileWithoutQuery),
      line: Number(line),
      column: Number(column),
      source_content: '',
      start_line: 0,
      end_line: 0,
      source_content_lines: [],
    } as StackObject;
  }

  const rawSourceMap = (await response.json()) as sourceMap.RawSourceMap;

  if (!rawSourceMap.file) {
    return {
      method: methodName,
      file: getRelativePath(fileWithoutQuery),
      line: Number(line),
      column: Number(column),
      source_content: '',
      start_line: 0,
      end_line: 0,
      source_content_lines: [],
    } as StackObject;
  }

  const consumer = new sourceMap.SourceMapConsumer(rawSourceMap);
  const originalPosition = consumer.originalPositionFor({
    line: Number(line),
    column: Number(column),
  });

  const lines = consumer.sourceContentFor(originalPosition.source)?.split('\n') ?? [];

  if (lines.length === 0) {
    return {
      method: methodName,
      file: fileWithoutQuery,
      line: Number(line),
      column: Number(column),
      source_content: '',
      start_line: 0,
      end_line: 0,
      source_content_lines: [],
    } as StackObject;
  }

  const startLine = Math.max(0, originalPosition.line - 5);
  const endLine = Math.min(lines.length - 1, originalPosition.line + 5);

  const sourceContentLines = lines.slice(startLine, endLine + 1);

  return {
    method: methodName,
    file: originalPosition.source,
    line: originalPosition.line,
    column: originalPosition.column,
    source_content: consumer.sourceContentFor(originalPosition.source),
    start_line: startLine,
    end_line: endLine,
    source_content_lines: sourceContentLines,
  } as StackObject;
};

export const ErrorBoundary = () => {
  const error = useRouteError() as ErrorResponse;
  const [stackObjects, setStackObjects] = useState<StackObject[]>([]);

  useEffect(() => {
    void (async () => {
      const stackArray = errorStackToArray(error.stack ?? '');

      if (stackArray.length > 0) {
        const contents = await Promise.all(
          stackArray.slice(1).map(async (stack) => {
            return parseIndividualErrorStack(stack);
          }),
        );

        setStackObjects(contents);
      }
    })();
  }, [error.stack]);

  const getErrorMessage = () => {
    if (error.message) return error.message;
    if (error.statusText) return error.statusText;
    if (error.data) return error.data;
    return 'An unexpected error occurred';
  };

  const errorFileContent = useMemo(() => {
    if (stackObjects.length === 0) return null;
    return stackObjects[0];
  }, [stackObjects]);

  return (
    <div className="growfund-fixed growfund-inset-0 growfund-z-highest growfund-flex growfund-items-center growfund-justify-center growfund-bg-black/30 growfund-backdrop-blur-sm growfund-p-4 growfund-overflow-hidden ">
      <div className="growfund-w-full growfund-max-w-[var(--growfund-container-width)]">
        {/* Header with pagination */}
        <div className="growfund-flex growfund-items-center growfund-justify-between growfund-h-[2.625rem] growfund-relative growfund-top-[3px] growfund-z-positive">
          <div className="growfund-flex growfund-items-center growfund-gap-1 growfund-bg-white growfund-border-2 growfund-border-border growfund-border-b-0 growfund-border-r-0 growfund-rounded-tl-2xl growfund-rounded-tr-2xl growfund-px-4 growfund-h-full growfund-typo-tiny growfund-font-medium growfund-text-fg-brand growfund-relative">
            Growfund
            <svg
              width="60"
              height="42"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              className="growfund-absolute growfund-right-[-46px] growfund-top-[-2px]"
              preserveAspectRatio="none"
            >
              <mask
                id="b"
                maskUnits="userSpaceOnUse"
                x="0"
                y="-1"
                width="60"
                height="43"
                style={{ maskType: 'alpha' }}
              >
                <mask
                  id="a"
                  maskUnits="userSpaceOnUse"
                  x="0"
                  y="-1"
                  width="60"
                  height="43"
                  fill="none"
                >
                  <path fill="#fff" d="M0-1h60v43H0z" />
                  <path d="M1 0h7.078a20 20 0 0 1 18.033 11.35l8.778 18.3A20 20 0 0 0 52.922 41H60 1V0Z" />
                </mask>
                <path
                  d="M1 0h7.078a20 20 0 0 1 18.033 11.35l8.778 18.3A20 20 0 0 0 52.922 41H60 1V0Z"
                  fill="#fff"
                />
                <path
                  d="M1 0v-1H0v1h1Zm0 41H0v1h1v-1Zm33.889-11.35-.902.432.902-.432Zm-8.778-18.3.902-.432-.902.432ZM1 1h7.078v-2H1v2Zm59 39H1v2h59v-2ZM2 41V0H0v41h2Zm23.21-29.217 8.777 18.3 1.804-.866-8.778-18.3-1.804.866ZM52.921 42H60v-2h-7.078v2ZM33.987 30.082A21 21 0 0 0 52.922 42v-2A19 19 0 0 1 35.79 29.217l-1.804.865ZM8.078 1A19 19 0 0 1 25.21 11.783l1.804-.865A21 21 0 0 0 8.078-1v2Z"
                  fill="none"
                  mask="url(#a)"
                />
              </mask>
              <g mask="url(#b)">
                <mask
                  id="c"
                  maskUnits="userSpaceOnUse"
                  x="-1"
                  y=".024"
                  width="60"
                  height="43"
                  fill="none"
                >
                  <path fill="#fff" d="M-1 .024h60v43H-1z" />
                  <path d="M0 1.024h7.078a20 20 0 0 1 18.033 11.35l8.778 18.3a20 20 0 0 0 18.033 11.35H59 0v-41Z" />
                </mask>
                <path
                  d="M0 1.024h7.078a20 20 0 0 1 18.033 11.35l8.778 18.3a20 20 0 0 0 18.033 11.35H59 0v-41Z"
                  fill="#fff"
                />
                <path
                  d="M0 1.024v-1h-1v1h1Zm0 41h-1v1h1v-1Zm33.889-11.35-.902.433.902-.433Zm-8.778-18.3.902-.432-.902.433ZM0 2.025h7.078v-2H0v2Zm59 39H0v2h59v-2Zm-58 1v-41h-2v41h2Zm23.21-29.217 8.777 18.3 1.804-.865-8.778-18.3-1.804.865Zm27.712 30.217H59v-2h-7.078v2ZM32.987 31.107a21 21 0 0 0 18.935 11.917v-2A19 19 0 0 1 34.79 30.242l-1.804.865ZM7.078 2.024A19 19 0 0 1 24.21 12.807l1.804-.865A21 21 0 0 0 7.078.024v2Z"
                  fill="#e6e6e6"
                  mask="url(#c)"
                />
              </g>
            </svg>
          </div>

          <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-bg-white growfund-h-full growfund-border-2 growfund-border-border growfund-border-b-0 growfund-rounded-tl-2xl growfund-rounded-tr-2xl growfund-relative growfund-px-4">
            <div className="growfund-px-3 growfund-py-1 growfund-border growfund-border-border growfund-rounded-full growfund-flex growfund-items-center growfund-gap-1 growfund-shadow-md">
              <span className="growfund-typo-tiny growfund-font-medium growfund-text-gray-700">v1.0.0</span>
              <HeartHandshake className="growfund-size-3 growfund-text-icon-brand" />
              <span className="growfund-typo-tiny growfund-font-medium growfund-text-pink-500">Vite</span>
            </div>
            <svg
              width="60"
              height="42"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              className="growfund-absolute growfund-left-[-46px] growfund-top-[-2px]"
              preserveAspectRatio="none"
              style={{ transform: `rotateY(180deg)` }}
            >
              <mask
                id="b"
                maskUnits="userSpaceOnUse"
                x="0"
                y="-1"
                width="60"
                height="43"
                style={{ maskType: 'alpha' }}
              >
                <mask
                  id="a"
                  maskUnits="userSpaceOnUse"
                  x="0"
                  y="-1"
                  width="60"
                  height="43"
                  fill="none"
                >
                  <path fill="#fff" d="M0-1h60v43H0z" />
                  <path d="M1 0h7.078a20 20 0 0 1 18.033 11.35l8.778 18.3A20 20 0 0 0 52.922 41H60 1V0Z" />
                </mask>
                <path
                  d="M1 0h7.078a20 20 0 0 1 18.033 11.35l8.778 18.3A20 20 0 0 0 52.922 41H60 1V0Z"
                  fill="#fff"
                />
                <path
                  d="M1 0v-1H0v1h1Zm0 41H0v1h1v-1Zm33.889-11.35-.902.432.902-.432Zm-8.778-18.3.902-.432-.902.432ZM1 1h7.078v-2H1v2Zm59 39H1v2h59v-2ZM2 41V0H0v41h2Zm23.21-29.217 8.777 18.3 1.804-.866-8.778-18.3-1.804.866ZM52.921 42H60v-2h-7.078v2ZM33.987 30.082A21 21 0 0 0 52.922 42v-2A19 19 0 0 1 35.79 29.217l-1.804.865ZM8.078 1A19 19 0 0 1 25.21 11.783l1.804-.865A21 21 0 0 0 8.078-1v2Z"
                  fill="none"
                  mask="url(#a)"
                />
              </mask>
              <g mask="url(#b)">
                <mask
                  id="c"
                  maskUnits="userSpaceOnUse"
                  x="-1"
                  y=".024"
                  width="60"
                  height="43"
                  fill="none"
                >
                  <path fill="#fff" d="M-1 .024h60v43H-1z" />
                  <path d="M0 1.024h7.078a20 20 0 0 1 18.033 11.35l8.778 18.3a20 20 0 0 0 18.033 11.35H59 0v-41Z" />
                </mask>
                <path
                  d="M0 1.024h7.078a20 20 0 0 1 18.033 11.35l8.778 18.3a20 20 0 0 0 18.033 11.35H59 0v-41Z"
                  fill="#fff"
                />
                <path
                  d="M0 1.024v-1h-1v1h1Zm0 41h-1v1h1v-1Zm33.889-11.35-.902.433.902-.433Zm-8.778-18.3.902-.432-.902.433ZM0 2.025h7.078v-2H0v2Zm59 39H0v2h59v-2Zm-58 1v-41h-2v41h2Zm23.21-29.217 8.777 18.3 1.804-.865-8.778-18.3-1.804.865Zm27.712 30.217H59v-2h-7.078v2ZM32.987 31.107a21 21 0 0 0 18.935 11.917v-2A19 19 0 0 1 34.79 30.242l-1.804.865ZM7.078 2.024A19 19 0 0 1 24.21 12.807l1.804-.865A21 21 0 0 0 7.078.024v2Z"
                  fill="#e6e6e6"
                  mask="url(#c)"
                />
              </g>
            </svg>
          </div>
        </div>

        {/* Error content */}
        <div className="growfund-p-4 growfund-bg-white growfund-shadow-xl growfund-rounded-bl-2xl growfund-rounded-br-2xl growfund-border-2 growfund-border-border growfund-z-negative growfund-space-y-6 growfund-overflow-y-auto growfund-max-h-[60svh] growfund-min-h-[12.5rem]">
          <Badge variant="destructive">Console Error</Badge>
          <p className="growfund-typo-small growfund-mb-4 growfund-text-fg-critical">{getErrorMessage()}</p>

          {/* Code frame */}
          <div className="growfund-mb-6 growfund-overflow-hidden growfund-rounded-md growfund-border growfund-border-gray-200 growfund-bg-gray-50">
            <div className="growfund-flex growfund-items-center growfund-justify-between growfund-border-b growfund-border-border growfund-bg-background-surface-secondary growfund-px-4 growfund-py-2">
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <FileCode2 className="growfund-size-4 growfund-text-icon-secondary" />
                <span className="growfund-typo-tiny growfund-text-fg-secondary">
                  {`${getRelativePath(errorFileContent?.file ?? '')} (${errorFileContent?.line}:${
                    errorFileContent?.column
                  }) @ ${errorFileContent?.method}`}
                </span>
              </div>
              <Button variant="ghost" size="icon">
                <ExternalLink className="growfund-h-4 growfund-w-4 growfund-text-gray-500" />
              </Button>
            </div>
            <Highlight
              theme={themes.github}
              code={errorFileContent?.source_content_lines.join('\n') ?? ''}
              language="tsx"
            >
              {({ className, style, tokens, getLineProps, getTokenProps }) => {
                return (
                  <pre style={style} className={cn('growfund-break-words growfund-ps-2', className)}>
                    {tokens.map((line, idx) => {
                      const fileLineNumber = (errorFileContent?.start_line ?? 0) + idx + 1;
                      const isCurrentLine = fileLineNumber === errorFileContent?.line;

                      return (
                        <div key={idx}>
                          <div
                            {...getLineProps({ line })}
                            className="growfund-flex growfund-items-center growfund-gap-4"
                          >
                            <div className="growfund-flex growfund-items-center growfund-gap-2">
                              <span className="growfund-w-2 growfund-text-fg-critical">
                                {isCurrentLine && '>'}
                              </span>
                              <span className="growfund-w-4 growfund-inline-block">{fileLineNumber}</span>
                              <span>|</span>
                            </div>
                            <div className="growfund-flex growfund-items-center growfund-typo-tiny">
                              {line.map((token, key) => (
                                <span
                                  key={key}
                                  {...getTokenProps({ token })}
                                  className="growfund-inline-block"
                                />
                              ))}
                            </div>
                          </div>

                          {isCurrentLine && (
                            <div
                              {...getLineProps({ line })}
                              className="growfund-flex growfund-items-center growfund-gap-4"
                            >
                              <div className="growfund-flex growfund-items-center growfund-gap-2">
                                <span className="growfund-w-2 growfund-text-fg-critical"></span>
                                <span className="growfund-w-4 growfund-inline-block"></span>
                                <span>|</span>
                              </div>
                              <div className="growfund-flex growfund-items-center growfund-typo-tiny growfund-text-fg-critical">
                                {' '.repeat(errorFileContent.column) + '^'}
                              </div>
                            </div>
                          )}
                        </div>
                      );
                    })}
                  </pre>
                );
              }}
            </Highlight>
          </div>

          {/* Call stack */}
          <div>
            <div className="growfund-mb-2 growfund-flex growfund-items-center growfund-justify-between">
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <p className="growfund-typo-small growfund-font-semibold growfund-text-gray-800">Call Stack</p>
                <Badge variant="secondary" className="growfund-rounded-full">
                  {stackObjects.length - 1}
                </Badge>
              </div>
            </div>

            <div className="growfund-space-y-2">
              {stackObjects.slice(1).map((stack, index) => {
                return (
                  <div key={index} className="growfund-px-3 growfund-py-2 growfund-grid growfund-gap-2">
                    <span className="growfund-font-medium growfund-text-fg-primary">{stack.method}</span>
                    <span className="growfund-typo-tiny growfund-text-fg-disabled">
                      {stack.file} ({stack.line}:{stack.column})
                    </span>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
